<?php

class MenuModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลเมนูทั้งหมด
    public function readMenu()
    {
        $query = "SELECT * FROM users.tb_menus WHERE is_active = true ORDER BY sort_order ASC, id ASC";
        $result = pg_query($this->conn, $query);

        if (!$result) {
            // error_log("Menu query error: " . pg_last_error($this->conn));
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }

    // ฟังก์ชันในการดึงข้อมูลเมนูแบบ hierarchical (tree structure)
    public function readMenuTree()
    {
        $menus = $this->readMenu();
        if (!$menus) {
            return [];
        }

        // สร้าง index ตาม id
        $menuIndex = [];
        foreach ($menus as $menu) {
            $menuId = (int)$menu['id'];
            $menuIndex[$menuId] = $menu;
            $menuIndex[$menuId]['children'] = [];
        }

        // สร้าง tree structure
        $tree = [];
        foreach ($menus as $menu) {
            $menuId = (int)$menu['id'];
            $parentId = !empty($menu['parent_id']) ? (int)$menu['parent_id'] : null;
            
            if ($parentId === null || $parentId === 0) {
                // Root menu
                $tree[] = &$menuIndex[$menuId];
            } else {
                // Child menu
                if (isset($menuIndex[$parentId])) {
                    $menuIndex[$parentId]['children'][] = &$menuIndex[$menuId];
                }
            }
        }

        // เรียงลำดับ children ตาม sort_order
        $sortChildren = function(&$menu) use (&$sortChildren) {
            if (!empty($menu['children'])) {
                usort($menu['children'], function($a, $b) {
                    $sortA = isset($a['sort_order']) ? (int)$a['sort_order'] : 999;
                    $sortB = isset($b['sort_order']) ? (int)$b['sort_order'] : 999;
                    if ($sortA === $sortB) {
                        return (int)$a['id'] - (int)$b['id'];
                    }
                    return $sortA - $sortB;
                });
                foreach ($menu['children'] as &$child) {
                    $sortChildren($child);
                }
            }
        };

        foreach ($tree as &$rootMenu) {
            $sortChildren($rootMenu);
        }

        // เรียงลำดับ root menus ตาม sort_order
        usort($tree, function($a, $b) {
            $sortA = isset($a['sort_order']) ? (int)$a['sort_order'] : 999;
            $sortB = isset($b['sort_order']) ? (int)$b['sort_order'] : 999;
            if ($sortA === $sortB) {
                return (int)$a['id'] - (int)$b['id'];
            }
            return $sortA - $sortB;
        });

        return $tree;
    }

    // ฟังก์ชันในการดึงข้อมูลเมนูตาม position_id
    public function readMenuByPositionIds($positionIds)
    {
        if (empty($positionIds)) {
            // error_log("MenuModel: readMenuByPositionIds called with empty positionIds");
            return [];
        }

        // แปลง array เป็น string สำหรับ IN clause
        $positionIdsStr = implode(',', array_map('intval', $positionIds));
        // error_log("MenuModel: Filtering menus for position IDs: " . $positionIdsStr);

        // ดึงเมนูที่ position มีสิทธิ์เข้าถึง
        // Logic สำหรับหลาย position_ids (Union - แสดงเมนูถ้าอย่างน้อย 1 position_id มีสิทธิ์):
        // แสดงเมนูถ้า:
        //   1. ไม่มี permission record ใดๆ (default visible)
        //   2. อย่างน้อย 1 position_id มี is_granted = true
        //   3. ไม่มี permission record สำหรับ position_ids ใดๆ (default visible)
        // ไม่แสดงเมนูถ้า:
        //   - มี permission record สำหรับ position_ids อย่างน้อย 1 ตัว
        //   - และทุก permission record มี is_granted = false
        //   - และไม่มี position_id ใดที่ไม่มี permission record
        $query = "
            SELECT DISTINCT m.*
            FROM users.tb_menus m
            WHERE m.is_active = true
            AND (
                -- กรณี 1: เมนูที่ไม่มี permission record ใดๆ (แสดงให้ทุกคน)
                m.id NOT IN (
                    SELECT DISTINCT menu_id 
                    FROM users.tb_menu_permissions
                )
                OR
                -- กรณี 2: เมนูที่อย่างน้อย 1 position_id มีสิทธิ์ (is_granted = true)
                EXISTS (
                    SELECT 1
                    FROM users.tb_menu_permissions mp 
                    WHERE mp.menu_id = m.id
                    AND mp.position_id IN ($positionIdsStr) 
                    AND mp.is_granted = true
                )
                OR
                -- กรณี 3: เมนูที่ไม่มี permission record สำหรับ position_ids ใดๆ
                -- (แม้ว่าจะมี permission record สำหรับ position_ids อื่นๆ = default visible)
                NOT EXISTS (
                    SELECT 1
                    FROM users.tb_menu_permissions mp 
                    WHERE mp.menu_id = m.id
                    AND mp.position_id IN ($positionIdsStr)
                )
            )
            ORDER BY m.sort_order ASC, m.id ASC
        ";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            $error = pg_last_error($this->conn);
            // error_log("MenuModel: Menu by position query error: " . $error);
            // error_log("MenuModel: Query was: " . $query);
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        // error_log("MenuModel: Found " . count($datas) . " menus for position IDs: " . $positionIdsStr);
        
        // Debug: Log menu IDs for each position
        foreach ($positionIds as $posId) {
            $queryDebug = "
                SELECT COUNT(*) as count
                FROM users.tb_menus m
                WHERE m.is_active = true
                AND (
                    m.id NOT IN (SELECT DISTINCT menu_id FROM users.tb_menu_permissions)
                    OR EXISTS (
                        SELECT 1 FROM users.tb_menu_permissions mp 
                        WHERE mp.menu_id = m.id AND mp.position_id = $posId AND mp.is_granted = true
                    )
                    OR NOT EXISTS (
                        SELECT 1 FROM users.tb_menu_permissions mp 
                        WHERE mp.menu_id = m.id AND mp.position_id = $posId
                    )
                )
            ";
            $resultDebug = pg_query($this->conn, $queryDebug);
            if ($resultDebug) {
                $rowDebug = pg_fetch_assoc($resultDebug);
                // error_log("MenuModel: Position ID $posId would see " . $rowDebug['count'] . " menus individually");
            }
        }
        
        return $datas;
    }

    // ฟังก์ชันในการดึงข้อมูลเมนูแบบ hierarchical ตาม position_id
    public function readMenuTreeByPositionIds($positionIds)
    {
        $menus = $this->readMenuByPositionIds($positionIds);
        if (!$menus) {
            return [];
        }

        // ดึง parent menus ที่จำเป็น (ถ้า child มีสิทธิ์ แต่ parent ไม่มีสิทธิ์)
        $parentIds = [];
        foreach ($menus as $menu) {
            if (!empty($menu['parent_id']) && $menu['parent_id'] !== null && $menu['parent_id'] !== '') {
                $parentId = (int)$menu['parent_id'];
                // ตรวจสอบว่า parent อยู่ใน menus แล้วหรือไม่
                $parentExists = false;
                foreach ($menus as $existingMenu) {
                    if ((int)$existingMenu['id'] === $parentId) {
                        $parentExists = true;
                        break;
                    }
                }
                if (!$parentExists) {
                    $parentIds[$parentId] = $parentId;
                }
            }
        }

        // ดึง parent menus ที่ขาดหายไป
        if (!empty($parentIds)) {
            $parentIdsStr = implode(',', $parentIds);
            $query = "
                SELECT m.*
                FROM users.tb_menus m
                WHERE m.id IN ($parentIdsStr)
                AND m.is_active = true
                ORDER BY m.sort_order ASC, m.id ASC
            ";
            $result = pg_query($this->conn, $query);
            if ($result) {
                while ($row = pg_fetch_assoc($result)) {
                    $menus[] = $row;
                }
            }
        }

        // สร้าง index ตาม id
        $menuIndex = [];
        foreach ($menus as $menu) {
            $menuId = (int)$menu['id'];
            $menuIndex[$menuId] = $menu;
            $menuIndex[$menuId]['children'] = [];
        }

        // สร้าง tree structure
        $tree = [];
        foreach ($menus as $menu) {
            $menuId = (int)$menu['id'];
            // แปลง parent_id เป็น integer (อาจเป็น string จาก database)
            $parentId = null;
            if (!empty($menu['parent_id']) && $menu['parent_id'] !== null && $menu['parent_id'] !== '') {
                $parentId = (int)$menu['parent_id'];
            }
            
            if ($parentId === null || $parentId === 0 || !isset($menuIndex[$parentId])) {
                // Root menu หรือ parent ไม่มีสิทธิ์
                if (!isset($menuIndex[$menuId]['_added_to_tree'])) {
                    $tree[] = &$menuIndex[$menuId];
                    $menuIndex[$menuId]['_added_to_tree'] = true;
                }
            } else {
                // Child menu - เพิ่มเข้าไปใน parent
                if (isset($menuIndex[$parentId])) {
                    $menuIndex[$parentId]['children'][] = &$menuIndex[$menuId];
                    $menuIndex[$menuId]['_added_to_tree'] = true;
                } else {
                    // Parent ไม่มีใน index (ไม่น่าจะเกิดขึ้น แต่เพิ่มไว้เพื่อความปลอดภัย)
                    if (!isset($menuIndex[$menuId]['_added_to_tree'])) {
                        $tree[] = &$menuIndex[$menuId];
                        $menuIndex[$menuId]['_added_to_tree'] = true;
                    }
                }
            }
        }

        // เรียงลำดับ children ตาม sort_order
        $sortChildren = function(&$menu) use (&$sortChildren) {
            if (!empty($menu['children'])) {
                usort($menu['children'], function($a, $b) {
                    $sortA = isset($a['sort_order']) ? (int)$a['sort_order'] : 999;
                    $sortB = isset($b['sort_order']) ? (int)$b['sort_order'] : 999;
                    if ($sortA === $sortB) {
                        return (int)$a['id'] - (int)$b['id'];
                    }
                    return $sortA - $sortB;
                });
                foreach ($menu['children'] as &$child) {
                    $sortChildren($child);
                }
            }
        };

        foreach ($tree as &$rootMenu) {
            $sortChildren($rootMenu);
        }

        // เรียงลำดับ root menus ตาม sort_order
        usort($tree, function($a, $b) {
            $sortA = isset($a['sort_order']) ? (int)$a['sort_order'] : 999;
            $sortB = isset($b['sort_order']) ? (int)$b['sort_order'] : 999;
            if ($sortA === $sortB) {
                return (int)$a['id'] - (int)$b['id'];
            }
            return $sortA - $sortB;
        });

        return $tree;
    }

    // ฟังก์ชันในการดึงข้อมูลของ menu หนึ่งรายการ
    public function readMenuOne($id)
    {
        $query = "SELECT * FROM users.tb_menus WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            // error_log("Menu query error: " . pg_last_error($this->conn));
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันในการดึงข้อมูล menu ตาม menu_code
    public function readMenuByCode($menuCode)
    {
        $query = "SELECT * FROM users.tb_menus WHERE menu_code = $1";
        $result = pg_query_params($this->conn, $query, array($menuCode));

        if (!$result) {
            // error_log("Menu query error: " . pg_last_error($this->conn));
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันในการดึงข้อมูลเมนูตาม category
    public function readMenuByCategory($category)
    {
        $query = "SELECT * FROM users.tb_menus WHERE category = $1 AND is_active = true ORDER BY sort_order ASC, id ASC";
        $result = pg_query_params($this->conn, $query, array($category));

        if (!$result) {
            // error_log("Menu query error: " . pg_last_error($this->conn));
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }

    // ฟังก์ชันสำหรับการสร้าง menu ใหม่
    public function createMenu($menuCode, $menuName, $menuPath = null, $parentId = null, $icon = null, $sortOrder = 0, $category = null, $isActive = true)
    {
        // ตรวจสอบว่า menu_code ซ้ำหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_menus WHERE menu_code = $1";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($menuCode));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            // error_log("Error: The menu code is already taken: " . $menuCode);
            return false;
        }

        // Insert ข้อมูลใหม่
        $query = "INSERT INTO users.tb_menus (menu_code, menu_name, menu_path, parent_id, icon, sort_order, category, is_active) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id";
        $result = pg_query_params($this->conn, $query, array(
            $menuCode, $menuName, $menuPath, $parentId, $icon, $sortOrder, $category, $isActive ? 'true' : 'false'
        ));

        if (!$result) {
            // error_log("Menu insert error: " . pg_last_error($this->conn));
            return false;
        }

        $row = pg_fetch_assoc($result);
        return $row['id']; // คืนค่า id ที่สร้าง
    }

    // ฟังก์ชันสำหรับการอัปเดตข้อมูล menu
    public function updateMenu($id, $menuCode, $menuName, $menuPath = null, $parentId = null, $icon = null, $sortOrder = 0, $category = null, $isActive = true)
    {
        // ตรวจสอบว่า menu_code ซ้ำกับ id อื่นหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_menus WHERE menu_code = $1 AND id != $2";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($menuCode, $id));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            // error_log("Error: The menu code is already taken by another menu: " . $menuCode);
            return false;
        }

        // Update ข้อมูล
        $query = "UPDATE users.tb_menus 
                  SET menu_code = $1, menu_name = $2, menu_path = $3, parent_id = $4, 
                      icon = $5, sort_order = $6, category = $7, is_active = $8 
                  WHERE id = $9";
        $result = pg_query_params($this->conn, $query, array(
            $menuCode, $menuName, $menuPath, $parentId, $icon, $sortOrder, $category, $isActive ? 'true' : 'false', $id
        ));

        if (!$result) {
            // error_log("Menu update error: " . pg_last_error($this->conn));
            return false;
        }

        return true;
    }

    // ฟังก์ชันสำหรับการลบ menu (soft delete)
    public function deleteMenu($id)
    {
        $query = "UPDATE users.tb_menus SET is_active = false WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            // error_log("Menu delete error: " . pg_last_error($this->conn));
            return false;
        }

        return true;
    }

    // ฟังก์ชันสำหรับการลบ menu แบบ hard delete
    public function hardDeleteMenu($id)
    {
        // ตรวจสอบว่ามี children หรือไม่
        $queryCheckChildren = "SELECT COUNT(*) FROM users.tb_menus WHERE parent_id = $1";
        $resultChildren = pg_query_params($this->conn, $queryCheckChildren, array($id));
        $rowChildren = pg_fetch_assoc($resultChildren);

        if ($rowChildren['count'] > 0) {
            // error_log("Error: Cannot delete menu with children");
            return false;
        }

        $query = "DELETE FROM users.tb_menus WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            // error_log("Menu hard delete error: " . pg_last_error($this->conn));
            return false;
        }

        return true;
    }

    // ฟังก์ชันสำหรับดึง categories ทั้งหมด
    public function readCategories()
    {
        $query = "SELECT DISTINCT category FROM users.tb_menus WHERE category IS NOT NULL AND is_active = true ORDER BY category";
        $result = pg_query($this->conn, $query);

        if (!$result) {
            // error_log("Categories query error: " . pg_last_error($this->conn));
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row['category'];
        }

        return $datas;
    }
}


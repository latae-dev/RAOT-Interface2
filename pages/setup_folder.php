<?php
/**
 * Setup Storage Folders for Investment Budget
 * รันไฟล์นี้ครั้งเดียวเพื่อสร้างโฟลเดอร์ทั้งหมดที่ต้องใช้
 */

$baseDir = __DIR__ . '../../storage/investment_budget';
$folders = [
    $baseDir,
    $baseDir . '/sap'
];

echo "Creating storage folders...\n";
echo str_repeat("=", 50) . "\n\n";

foreach ($folders as $folder) {
    echo "Creating: $folder\n";
    
    if (is_dir($folder)) {
        echo "  ✓ Already exists\n";
    } else {
        $oldUmask = umask(0);
        $created = mkdir($folder, 0777, true);
        umask($oldUmask);
        
        if ($created) {
            echo "  ✓ Created successfully\n";
            
            // Windows: ตั้งค่า permission เพิ่มเติม
            if (DIRECTORY_SEPARATOR === '\\') {
                chmod($folder, 0777);
            }
        } else {
            echo "  ✗ Failed to create\n";
            echo "  Error: " . error_get_last()['message'] . "\n";
        }
    }
    
    // ตรวจสอบ permission
    if (is_dir($folder)) {
        if (is_writable($folder)) {
            echo "  ✓ Writable: Yes\n";
        } else {
            echo "  ✗ Writable: No (Please check Windows folder permissions)\n";
        }
    }
    
    echo "\n";
}

echo str_repeat("=", 50) . "\n";
echo "Done!\n\n";

// บน Windows แสดงคำแนะนำ
if (DIRECTORY_SEPARATOR === '\\') {
    echo "Windows Server Instructions:\n";
    echo "If folders are not writable, please:\n";
    echo "1. Right-click each folder → Properties → Security\n";
    echo "2. Click Edit → Add → Enter 'IIS_IUSRS' and 'IUSR'\n";
    echo "3. Check 'Full Control' or at least 'Modify, Write'\n";
    echo "4. Click Apply\n\n";
    echo "Or run this PowerShell command as Administrator:\n";
    echo "icacls \"" . realpath($baseDir) . "\" /grant \"IIS_IUSRS:(OI)(CI)F\" /T\n";
    echo "icacls \"" . realpath($baseDir) . "\" /grant \"IUSR:(OI)(CI)F\" /T\n";
}

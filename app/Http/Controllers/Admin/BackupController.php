<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackupController extends Controller
{
    public function index()
    {
        return view('admin.settings.backup');
    }

    public function downloadBackup()
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE', 'new_erp');
        $key = 'Tables_in_' . $dbName;
        
        $sqlDump = "-- HisabMittra ERP+CRM Database Backup\n";
        $sqlDump .= "-- Generated on " . now()->toDateTimeString() . "\n\n";
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$key;
            
            // Structure
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $sqlDump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sqlDump .= $createTable->{'Create Table'} . ";\n\n";
            
            // Data
            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $rowArray = (array)$row;
                $keys = array_map(function($k) { return "`{$k}`"; }, array_keys($rowArray));
                $values = array_map(function($v) {
                    if (is_null($v)) return 'NULL';
                    return DB::getPdo()->quote($v);
                }, array_values($rowArray));
                
                $sqlDump .= "INSERT INTO `{$tableName}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sqlDump .= "\n";
        }
        
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
        
        return response($sqlDump)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file',
        ]);

        $file = $request->file('backup_file');
        $sqlContent = file_get_contents($file->getRealPath());

        try {
            DB::unprepared($sqlContent);
            return redirect()->route('admin.settings.backup')->with('success', 'Database restored successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.backup')->with('error', 'Failed to restore database: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Backup;
use Illuminate\Console\Command;

class PanelBackup extends Command
{
    protected $signature = 'panel:backup';
    protected $description = 'Create a database backup';

    public function handle(): int
    {
        $fileName = 'backup_' . date('Y-m-d_His') . '.sql';
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filePath = $backupDir . '/' . $fileName;

        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}");

        if ($dbConnection === 'sqlite') {
            $dbPath = $dbConfig['database'];
            if (file_exists($dbPath)) {
                copy($dbPath, $filePath);
                $this->info("SQLite database copied to: {$filePath}");
            } else {
                $this->error("SQLite database not found: {$dbPath}");
                return self::FAILURE;
            }
        } else {
            $command = sprintf(
                'mysqldump -h%s -P%s -u%s %s %s > %s 2>&1',
                escapeshellarg($dbConfig['host'] ?? '127.0.0.1'),
                escapeshellarg($dbConfig['port'] ?? '3306'),
                escapeshellarg($dbConfig['username'] ?? 'root'),
                !empty($dbConfig['password']) ? '-p' . escapeshellarg($dbConfig['password']) : '',
                escapeshellarg($dbConfig['database'] ?? 'netaccess'),
                escapeshellarg($filePath)
            );

            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                $this->error('Backup failed: ' . implode("\n", $output));
                return self::FAILURE;
            }
        }

        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

        Backup::create([
            'file_name' => $fileName,
            'file_path' => 'backups/' . $fileName,
            'file_size' => $fileSize,
            'user_id' => auth()->id(),
        ]);

        $this->info("Backup created: {$fileName} ({$fileSize} bytes)");
        return self::SUCCESS;
    }
}

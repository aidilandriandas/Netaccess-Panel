<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class FullBackupController extends Controller
{
    private function backupDir(): string
    {
        return storage_path('app/full-backups');
    }

    public function index()
    {
        File::ensureDirectoryExists($this->backupDir());

        $files = collect(File::files($this->backupDir()))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.zip'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $this->humanSize($file->getSize()),
                'created' => date('d M Y H:i', $file->getMTime()),
            ])
            ->values();

        return view('backups.index', compact('files'));
    }

    public function store(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        File::ensureDirectoryExists($this->backupDir());

        $timestamp = now()->format('Ymd_His');
        $appName = preg_replace('/[^a-zA-Z0-9_\-]/', '-', config('app.name', 'netaccess'));
        $backupName = strtolower($appName) . '_full_backup_' . $timestamp . '.zip';
        $zipPath = $this->backupDir() . '/' . $backupName;

        $tmpDir = storage_path('app/full-backups/tmp_' . $timestamp);
        File::ensureDirectoryExists($tmpDir);

        try {
            $sqlFile = $tmpDir . '/database_' . $timestamp . '.sql';
            $this->dumpDatabase($sqlFile);

            $noteFile = $tmpDir . '/RESTORE-NOTE.txt';
            File::put($noteFile, $this->restoreNote($timestamp));

            $zip = new ZipArchive();

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Gagal membuat file ZIP.');
            }

            $root = base_path();

            $exclude = [
                '/.git',
                '/node_modules',
                '/storage/app/full-backups',
                '/storage/framework/cache',
                '/storage/framework/sessions',
                '/storage/framework/views',
            ];

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                $path = $file->getRealPath();

                if (!$path) {
                    continue;
                }

                $relativePath = str_replace($root, '', $path);
                $relativePath = str_replace('\\', '/', $relativePath);

                $skip = false;
                foreach ($exclude as $ex) {
                    if (str_starts_with($relativePath, $ex)) {
                        $skip = true;
                        break;
                    }
                }

                if ($skip) {
                    continue;
                }

                $zipName = 'web' . $relativePath;

                if ($file->isDir()) {
                    $zip->addEmptyDir($zipName);
                } else {
                    $zip->addFile($path, $zipName);
                }
            }

            $zip->addFile($sqlFile, 'database/database_' . $timestamp . '.sql');
            $zip->addFile($noteFile, 'RESTORE-NOTE.txt');

            $zip->close();

            File::deleteDirectory($tmpDir);

            return redirect()
                ->route('admin.full-backups.index')
                ->with('success', 'Backup full berhasil dibuat: ' . $backupName);
        } catch (\Throwable $e) {
            if (File::exists($zipPath)) {
                File::delete($zipPath);
            }

            if (File::exists($tmpDir)) {
                File::deleteDirectory($tmpDir);
            }

            return redirect()
                ->route('admin.full-backups.index')
                ->with('error', 'Backup gagal: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $path = $this->backupDir() . '/' . basename($filename);

        abort_unless(File::exists($path), 404);

        return response()->download($path);
    }

    public function destroy(string $filename)
    {
        $path = $this->backupDir() . '/' . basename($filename);

        if (File::exists($path)) {
            File::delete($path);
        }

        return redirect()
            ->route('admin.full-backups.index')
            ->with('success', 'Backup berhasil dihapus.');
    }

    private function dumpDatabase(string $targetFile): void
    {
        $connection = config('database.default');
        $db = config("database.connections.$connection");

        if (!$db || ($db['driver'] ?? null) !== 'mysql') {
            throw new \Exception('Backup database saat ini hanya support MySQL.');
        }

        $host = $db['host'] ?? '127.0.0.1';
        $port = $db['port'] ?? '3306';
        $database = $db['database'] ?? null;
        $username = $db['username'] ?? null;
        $password = $db['password'] ?? '';

        if (!$database || !$username) {
            throw new \Exception('Konfigurasi database tidak lengkap.');
        }

        $mysqldump = trim((string) shell_exec('command -v mysqldump'));

        if (!$mysqldump) {
            throw new \Exception('mysqldump belum terinstall. Jalankan: apt install mysql-client');
        }

        $command = sprintf(
            'MYSQL_PWD=%s %s --single-transaction --quick --lock-tables=false -h %s -P %s -u %s %s > %s 2>&1',
            escapeshellarg($password),
            escapeshellcmd($mysqldump),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($targetFile)
        );

        exec($command, $output, $code);

        if ($code !== 0 || !File::exists($targetFile) || File::size($targetFile) < 10) {
            throw new \Exception('mysqldump gagal. Cek user/password database di .env.');
        }
    }

    private function restoreNote(string $timestamp): string
    {
        return <<<TXT
NETACCESS FULL BACKUP
Created: {$timestamp}

Isi backup:
1. Folder web Laravel ada di folder /web
2. Database SQL ada di folder /database

Cara restore singkat:
1. Extract ZIP
2. Upload isi folder /web ke server
3. Import file .sql ke database MySQL
4. Sesuaikan file .env
5. Jalankan:
   composer install
   php artisan key:generate
   php artisan migrate --force
   php artisan optimize:clear

PENTING:
File backup ini berisi .env dan database, jangan taruh di public_html.
TXT;
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

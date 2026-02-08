<?php

namespace Ksfraser\FA_Hooks\Install;

use Exception;

/**
 * Composer Installer for FA-Hooks Module
 *
 * Handles automatic installation of PHP dependencies during module installation.
 * This ensures all required libraries are available when the hook system is activated.
 */
class ComposerInstaller
{
    /** @var string */
    private $modulePath;

    /** @var string */
    private $composerJsonPath;

    /** @var string */
    private $vendorPath;

    /**
     * Constructor
     *
     * @param string $modulePath Absolute path to the module directory
     */
    public function __construct(string $modulePath)
    {
        $this->modulePath = rtrim($modulePath, DIRECTORY_SEPARATOR);
        $this->composerJsonPath = $this->modulePath . DIRECTORY_SEPARATOR . 'composer.json';
        $this->vendorPath = $this->modulePath . DIRECTORY_SEPARATOR . 'vendor';
    }

    /**
     * Install composer dependencies
     *
     * @return array ['success' => bool, 'message' => string, 'output' => string]
     */
    public function install(): array
    {
        try {
            // Check if composer.json exists
            if (!file_exists($this->composerJsonPath)) {
                return [
                    'success' => false,
                    'message' => 'composer.json not found in module directory',
                    'output' => ''
                ];
            }

            // Check if composer is available
            $composerCheck = $this->runCommand('composer --version');
            if ($composerCheck['exit_code'] !== 0) {
                return [
                    'success' => false,
                    'message' => 'Composer is not installed or not in PATH',
                    'output' => $composerCheck['output']
                ];
            }

            // Run composer install
            $result = $this->runCommand('composer install --no-dev --optimize-autoloader', $this->modulePath);

            if ($result['exit_code'] === 0) {
                // Verify vendor directory was created
                if (is_dir($this->vendorPath)) {
                    return [
                        'success' => true,
                        'message' => 'Composer dependencies installed successfully',
                        'output' => $result['output']
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Composer install completed but vendor directory not found',
                        'output' => $result['output']
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Composer install failed',
                    'output' => $result['output']
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception during installation: ' . $e->getMessage(),
                'output' => ''
            ];
        }
    }

    /**
     * Run a shell command
     *
     * @param string $command The command to run
     * @param string|null $workingDir Working directory (optional)
     * @return array ['exit_code' => int, 'output' => string]
     */
    private function runCommand(string $command, ?string $workingDir = null): array
    {
        $output = '';
        $exitCode = 0;

        // Set up execution environment
        $cwd = getcwd();
        if ($workingDir && is_dir($workingDir)) {
            chdir($workingDir);
        }

        try {
            // Execute command and capture output
            $output = shell_exec($command . ' 2>&1');
            $exitCode = $this->getExitCode($output);

            // Restore working directory
            if ($workingDir) {
                chdir($cwd);
            }

        } catch (Exception $e) {
            $output = 'Error executing command: ' . $e->getMessage();
            $exitCode = 1;

            // Restore working directory
            if ($workingDir) {
                chdir($cwd);
            }
        }

        return [
            'exit_code' => $exitCode,
            'output' => $output ?: ''
        ];
    }

    /**
     * Extract exit code from command output (basic implementation)
     *
     * @param string $output Command output
     * @return int Exit code (0 for success, 1 for failure)
     */
    private function getExitCode(string $output): int
    {
        // Basic check - if output contains error indicators, assume failure
        if (stripos($output, 'error') !== false ||
            stripos($output, 'failed') !== false ||
            stripos($output, 'exception') !== false) {
            return 1;
        }

        return 0;
    }

    /**
     * Check if dependencies are already installed
     *
     * @return bool True if vendor directory exists and appears to be populated
     */
    public function isInstalled(): bool
    {
        return is_dir($this->vendorPath) &&
               file_exists($this->vendorPath . DIRECTORY_SEPARATOR . 'autoload.php');
    }

    /**
     * Get the vendor autoload path
     *
     * @return string|null Path to autoload.php or null if not installed
     */
    public function getAutoloadPath(): ?string
    {
        $autoloadPath = $this->vendorPath . DIRECTORY_SEPARATOR . 'autoload.php';
        return file_exists($autoloadPath) ? $autoloadPath : null;
    }
}
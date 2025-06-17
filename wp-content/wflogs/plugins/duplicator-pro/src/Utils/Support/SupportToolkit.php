<?php

namespace Duplicator\Utils\Support;

use DUP_PRO_Log;
use DUP_PRO_Package;
use DUP_PRO_Server;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Utils\ZipArchiveExtended;
use Exception;

class SupportToolkit
{
    const SUPPORT_TOOLKIT_BACKUP_NUMBER = 10;
    const SUPPORT_TOOLKIT_PREFIX        = 'duplicator_support_toolkit_';

    /**
     * Generates a support toolkit zip file
     *
     * @return string The path to the generated zip file
     */
    public static function getToolkit()
    {
        $tempZipFilePath = DUPLICATOR_PRO_SSDIR_PATH_TMP . '/' .
            self::SUPPORT_TOOLKIT_PREFIX . date(DUP_PRO_Package::PACKAGE_HASH_DATE_FORMAT) . '_' .
            SnapUtil::generatePassword(16, false, false) . '.zip';
        $zip             = new ZipArchiveExtended($tempZipFilePath);
        if ($zip->open() === false) {
            throw new Exception(__('Failed to create zip file', 'duplicator-pro'));
        }

        // Trace log
        if (get_option('duplicator_pro_trace_log_enabled', false) !== false) {
            $zip->addFile(DUP_PRO_Log::getTraceFilepath());
        }

        // Debug log (if it exists)
        if (WP_DEBUG_LOG !== false) {
            if (is_bool(WP_DEBUG_LOG) && WP_DEBUG_LOG === true) {
                $zip->addFile(
                    trailingslashit(wp_normalize_path(realpath(WP_CONTENT_DIR))) . 'debug.log',
                    '',
                    10 * MB_IN_BYTES
                );
            } elseif (is_string(WP_DEBUG_LOG) && strlen(WP_DEBUG_LOG) > 0) {
                //The path can be relative too so resolve via safepath
                $zip->addFile(
                    SnapIO::safePath(WP_DEBUG_LOG, true),
                    '',
                    10 * MB_IN_BYTES
                );
            }
        }

        //phpinfo (as html)
        $zip->addFileFromString('phpinfo.html', self::getPhpInfo());

        //custom server settings info (as html)
        $zip->addFileFromString('serverinfo.txt', self::getPlainServerSettings());

        //Last 10 backup build logs
        DUP_PRO_Package::by_status_callback(
            function (DUP_PRO_Package $package) use ($zip) {
                $zip->addFile($package->get_safe_log_filepath());
            },
            [],
            self::SUPPORT_TOOLKIT_BACKUP_NUMBER,
            0,
            '`id` DESC'
        );

        return $tempZipFilePath;
    }

    /**
     * Returns the contents of the "Server Settings" section in "Tools" > "General" in plain text format
     *
     * @return string
     */
    private static function getPlainServerSettings()
    {
        $result = '';

        foreach (DUP_PRO_Server::getServerSettingsData() as $section) {
            $result .= $section['title'] . "\n";
            $result .= str_repeat('=', 50) . "\n";
            foreach ($section['settings'] as $data) {
                $result .= str_pad($data['logLabel'], 20, ' ', STR_PAD_RIGHT) . ' ' . $data['value'] . "\n";
            }
            $result .= "\n\n";
        }

        return $result;
    }

    /**
     * Returns the output of phpinfo as a string
     *
     * @return string
     */
    private static function getPhpInfo()
    {
        ob_start();
        SnapUtil::phpinfo();
        $phpInfo = ob_get_clean();

        return $phpInfo === false ? '' : $phpInfo;
    }
}

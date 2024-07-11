<?php

namespace Smartosc\Mpanel\Logger\Handler;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Base Logging Handler
 */
class Base extends BaseHandler
{
     const ROOT_DIRECTORY_NAME = 'cron_sync_promotion';

     const DEFAULT_PREFIX_LOG_FILE = 'cron_sync';

    /**
     * Logging handler constructor.
     *
     * @param DriverInterface $filesystem
     * @param Filesystem $coreFileSystem
     * @param DateTime $date
     * @param string|null $directoryName
     *
     * @throws Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        Filesystem $coreFileSystem,
        DateTime $date,
        $directoryName = null
    ) {
        $coreFileSystem = $coreFileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $directoryName = $coreFileSystem->getAbsolutePath(DirectoryList::LOG) . '/' .
            self::ROOT_DIRECTORY_NAME . '/' .
            ($directoryName ? $directoryName . '/' : '') .
            ($directoryName ?? self::DEFAULT_PREFIX_LOG_FILE) . '_' . $date->gmtDate('Ymd') . '.log';
        parent::__construct($filesystem, $directoryName);
    }
}

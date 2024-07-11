<?php

namespace Smartosc\Mpanel\Console;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DisableFrontendBuilder
 * @package Smartosc\Mpanel\Console
 */
class DisableFrontendBuilder extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Smartosc\Mpanel\Model\Management\Store
     */
    protected $homeStore;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Smartosc\Mpanel\Model\Management\Store $homeStore,
        $name = null
    ) {
        $this->homeStore = $homeStore;
        $this->state = $state;

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mpanel:front-builder:disable')
            ->setAliases(['mpanel:builder:disable'])
            ->setDescription('Disable frontend builder on all stores');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_setAreaCodeIfNotDefined();

        try {
            $this->homeStore->disableFrontendBuilder();
        } catch (AlreadyExistsException $e) {
            $output->writeln('AlreadyExistsException: ' . $e->getMessage());
        } catch (\Exception $e) {
            $output->writeln('Exception: ' . $e->getMessage());
        }
    }

    /**
     * @throws LocalizedException
     */
    private function _setAreaCodeIfNotDefined()
    {
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }
    }
}

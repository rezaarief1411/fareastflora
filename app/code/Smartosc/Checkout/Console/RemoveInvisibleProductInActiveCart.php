<?php

namespace Smartosc\Checkout\Console;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class RemoveInvisibleProductInActiveCart
 */
class RemoveInvisibleProductInActiveCart extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;
    
    /**
     * @var \Smartosc\Checkout\Model\Quote\CustomCart $customeCart
     */
    protected $customeCart;
    
    /**
     * RemoveInvisibleProductInActiveCart Constructor
     *
     * @param \Magento\Framework\App\State $state
     * @param \Smartosc\Checkout\Model\Quote\CustomCart $customeCart
     * @param string $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Smartosc\Checkout\Model\Quote\CustomCart $customeCart,
        string $name = null
    ) {
        $this->state = $state;
        $this->customeCart = $customeCart;
        parent::__construct($name);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption('customer_id', null, InputOption::VALUE_REQUIRED, 'Customer Id')
        ];
        
        $this->setName('smart:checkout:removeinvisibleproductfromcart')
        ->setAliases([])
        ->setDescription('Will remove invisible products from active cart. Accept customer ID as argument')
        ->setDefinition($options);
        
        parent::configure();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_setAreaCodeIfNotDefined();
        
        try {
            $customerId = $input->getOption('customer_id');
            $this->customeCart->setCustomerId($customerId)->removeInvisibleProductFromActiveCart();
        } catch (\Exception $e) {
            $output->writeln('Exception: ' . $e->getMessage());
        }
        
        return $this;
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

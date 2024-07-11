<?php


namespace Smartosc\Checkout\Model\Sequence;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\SalesSequence\Model\Meta;

class Sequence extends \Magento\SalesSequence\Model\Sequence
{
    /**
     * @var \Magento\SalesSequence\Model\Meta
     */
    protected $meta;

    protected $lastIncrementId;

    protected $resource;

    protected $activeProfile;

    public function __construct(
        Meta $meta,
        AppResource $resource,
        $pattern = \Magento\SalesSequence\Model\Sequence::DEFAULT_PATTERN
    ) {
        $this->meta = $meta;
        $this->resource = $resource;
        $this->activeProfile = $this->meta->getActiveProfile();

        \Magento\SalesSequence\Model\Sequence::__construct($meta, $resource, $pattern);
    }


    public function getCurrentValue()
    {


        $prefix = $this->meta->getActiveProfile()->getPrefix();
        $calcCurrentValue = $this->calculateCurrentValue();
        $metaId = $this->meta->getId();


        if (strlen($prefix) == 1) {
            $pattern = "%s%'.08d%s";
            return sprintf($pattern, $prefix, $calcCurrentValue, $this->meta->getActiveProfile()->getSuffix());
        } else {
            return parent::getCurrentValue();
        }
    }

    protected function calculateCurrentValue()
    {
        $connection = $this->resource->getConnection('sales');

        $lastIncrementId = $connection->lastInsertId($this->meta->getSequenceTable());

        return ($lastIncrementId - $this->meta->getActiveProfile()->getStartValue())
            * $this->meta->getActiveProfile()->getStep() + $this->meta->getActiveProfile()->getStartValue();
    }
}

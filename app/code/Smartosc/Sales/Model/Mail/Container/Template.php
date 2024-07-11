<?php

namespace Smartosc\Sales\Model\Mail\Container;

class Template extends \Magento\Sales\Model\Order\Email\Container\Template
{
    /**
     * @var array
     */
    protected $pdfAttachment;

    public function setPdfContent(String $pdfContent)
    {
        $this->pdfAttachment = $pdfContent;
    }

    public function getPdfContent()
    {
        return $this->pdfAttachment;
    }
}
<?php

namespace Centralbooks\ShipmentEmail\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mail\Template\TransportBuilder;

class TransportBuilderPlugin
{
    protected $resource;
    protected $templateVars = [];

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function beforeSetTemplateVars(TransportBuilder $subject, array $templateVars)
    {
        $this->templateVars = $templateVars;

        return [$templateVars];
    }

    public function beforeGetTransport(TransportBuilder $subject)
    {
        if (!isset($this->templateVars['order'])) {
            return;
        }

        $order = $this->templateVars['order'];

        $schoolId = $order->getData('school_id');

        if (!$schoolId) {
            return;
        }

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('schools_registered');

        $schoolEmail = $connection->fetchOne(
            "SELECT school_email FROM $table WHERE school_name = ?",
            $schoolId
        );

        if ($schoolEmail) {
            $subject->addBcc($schoolEmail);
        }
    }
}
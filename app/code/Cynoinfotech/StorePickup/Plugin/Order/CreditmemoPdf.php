<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Plugin\Order;

class CreditmemoPdf extends \Cynoinfotech\StorePickup\Plugin\AbstractPdf
{
    public function beforeGetPdf($subject, $shipments = [])
    {
        return parent::beforeGetPdf($subject, $shipments);
    }

    public function beforeInsertDocumentNumber($subject, $page, $text)
    {
        return parent::beforeInsertDocumentNumber($subject, $page, $text);
    }

    protected function getWhatShow()
    {
        return true;
    }

    protected function getPhrasePrefix()
    {
        return __('Credit Memo # ');
    }
}

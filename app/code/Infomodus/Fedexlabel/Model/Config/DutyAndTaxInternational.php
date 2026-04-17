<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by JetBrains PhpStorm.
 * User: Rudjuk Vitalij
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */

use Magento\Framework\Data\OptionSourceInterface;

class DutyAndTaxInternational implements OptionSourceInterface
{
    protected $account;
    public function __construct(\Infomodus\Fedexlabel\Model\AccountFactory $account)
    {
        $this->account = $account;
    }

    public function toOptionArray()
    {
        $c = [
            ['label' => __('shipper pays transportation fees and receiver pays duties and taxes'), 'value' => 'customer'],
            ['label' => __('shipper pays both transportation fees and duties and taxes'), 'value' => 'shipper'],
        ];

        $dhlAcctModel = $this->account->create()->getCollection();
        if (count($dhlAcctModel) > 0) {
            foreach ($dhlAcctModel as $u1) {
                $c[] = array('label' => 'shipper pays transportation fees and '.$u1->getCompanyname().' pays duties and taxes', 'value' => $u1->getId());
            }
        }

        return $c;
    }
}
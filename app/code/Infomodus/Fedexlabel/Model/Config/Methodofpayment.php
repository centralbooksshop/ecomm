<?php
namespace Infomodus\Fedexlabel\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class Methodofpayment implements OptionSourceInterface
{
    protected $accountModel;
    public function __construct(\Infomodus\Fedexlabel\Model\AccountFactory $accountModel)
    {
        $this->accountModel = $accountModel;
    }

    public function toOptionArray()
    {
        $c = array(
            array('label' => 'Shipper', 'value' => 'S'),
            /*array('label' => 'Recipient', 'value' => 'R'),*/
            /*array('label' => 'Third Party/Other', 'value' => 'T'),*/
        );
        $dhlAcctModel = $this->accountModel->create()->getCollection();
        if (count($dhlAcctModel) > 0) {
            foreach ($dhlAcctModel as $u1) {
                $c[] = array('label' => $u1->getCompanyname(), 'value' => $u1->getId());
            }
        }
        return $c;
    }
}
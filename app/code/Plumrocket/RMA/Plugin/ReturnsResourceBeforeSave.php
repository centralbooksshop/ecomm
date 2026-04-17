<?php
namespace Plumrocket\RMA\Plugin;

use Plumrocket\RMA\Model\Returns;
use Plumrocket\RMA\Model\ResourceModel\Returns as ReturnsResource;

class ReturnsResourceBeforeSave
{
    public function beforeSave(ReturnsResource $subject, Returns $returns)
    {
        $newNote = trim((string)$returns->getData('note'));
        $oldNote = trim((string)$returns->getOrigData('note'));

        /**
         * 1If DB already has a note DO NOT SAVE again
         */
        if ($oldNote !== '') {
            // restore original value to avoid update
            $returns->setData('note', $oldNote);
            return [$returns];
        }

        /**
         * First time only allow save if note provided
         */
        if ($newNote !== '') {
            $returns->setData('note', $newNote);
        }

        return [$returns];
    }
}

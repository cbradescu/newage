<?php

namespace CB\Bundle\NewAgeBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;

class CBNewAgeBundle implements Migration, AttachmentExtensionAwareInterface
{
    /** @var AttachmentExtension */
    protected $attachmentExtension;

    /**
     * {@inheritdoc}
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->attachmentExtension->addAttachmentAssociation(
            $schema,
            'cb_newage_panel_view', // entity table, e.g. oro_user, orocrm_contact etc.
            [], // optional, allowed MIME types of attached files, if empty - global configuration will be used
            10 // optional, max allowed file size in megabytes, by default 1 Mb
        );
    }
}
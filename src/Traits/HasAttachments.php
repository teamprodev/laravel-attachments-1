<?php

namespace DigitSoft\Attachments\Traits;

use Illuminate\Foundation\Auth\User;
use DigitSoft\Attachments\Attachment;
use DigitSoft\Attachments\AttachmentUsage;
use DigitSoft\Attachments\Facades\Attachments;
use DigitSoft\Attachments\Observers\AttachmentObserver;

/**
 * Trait HasAttachments
 * @package DigitSoft\Attachments\Traits
 * @property string       $primaryKey
 * @property Attachment[] $attachments
 */
trait HasAttachments
{
    /**
     * Get fields related to attachments.
     *
     * @return array
     */
    abstract public function getAttachableFields(): array;

    /**
     * Custom model type name (must be registered for morphing)
     * @var string|null
     */
    protected $attachments_model_type;

    /**
     *  Register Model observer.
     *
     * @return void
     */
    public static function bootHasAttachments()
    {
        static::observe(new AttachmentObserver());
    }

    /**
     * Get used attachments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function attachments()
    {
        return $this->morphToMany(
            Attachment::class,
            'model',
            'attachment_usages'
        );
    }

    /**
     * Get attachment usages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachmentUsages()
    {
        return $this->morphMany(AttachmentUsage::class, 'model');
    }

    /**
     * Add usage to attachment.
     *
     * @param Attachment|int $attachment
     */
    public function attachmentUse($attachment)
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $attachment = $attachment instanceof Attachment ? $attachment : Attachment::find($attachment);
        $id = $this->getUsageModelId();
        $type = $this->getUsageModelType();
        if ($id === null || $attachment === null) {
            return;
        }
        Attachments::addUsage($attachment, $id, $type);
    }

    /**
     * Remove attachment usage from this model.
     *
     * @param Attachment|int $attachment
     */
    public function attachmentForgetUsage($attachment)
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $attachment = $attachment instanceof Attachment ? $attachment : Attachment::find($attachment);
        $id = $this->getUsageModelId();
        $type = $this->getUsageModelType();
        if ($id === null || $attachment === null) {
            return;
        }
        Attachments::removeUsage($attachment, $id, $type);
    }

    /**
     * Fallback private attachment download check if model has no Policy.
     *
     * @param User       $user
     * @param Attachment $attachment
     * @return bool
     */
    public function attachmentCanDownload(User $user, Attachment $attachment)
    {
        return false;
    }

    /**
     * Get group for usage.
     *
     * @return string
     */
    private function getUsageModelType()
    {
        if ($this->attachments_model_type !== null) {
            return $this->attachments_model_type;
        }

        return get_called_class();
    }

    /**
     * Get ID for usage.
     *
     * @return string|int
     */
    private function getUsageModelId()
    {
        return $this->getKey();
    }

}

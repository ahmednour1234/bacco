<?php

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case QuotationSubmitted  = 'quotation_submitted';
    case QuotationAccepted   = 'quotation_accepted';
    case QuotationRejected   = 'quotation_rejected';
    case BoqSubmitted        = 'boq_submitted';
    case OrderCreated        = 'order_created';
    case OrderUpdated        = 'order_updated';
    case ProjectMilestone    = 'project_milestone';
    case ProductSubmitted    = 'product_submitted';
    case ProductApproved     = 'product_approved';
    case ProductRejected     = 'product_rejected';
    case General             = 'general';

    public function label(): string
    {
        return match ($this) {
            self::QuotationSubmitted  => 'Quotation Submitted',
            self::QuotationAccepted   => 'Quotation Accepted',
            self::QuotationRejected   => 'Quotation Rejected',
            self::BoqSubmitted        => 'BOQ Submitted',
            self::OrderCreated        => 'Order Created',
            self::OrderUpdated        => 'Order Updated',
            self::ProjectMilestone    => 'Project Milestone',
            self::ProductSubmitted    => 'Product Submitted for Approval',
            self::ProductApproved     => 'Product Approved',
            self::ProductRejected     => 'Product Rejected',
            self::General             => 'General',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::QuotationSubmitted  => 'quotation',
            self::QuotationAccepted   => 'success',
            self::QuotationRejected   => 'warning',
            self::BoqSubmitted        => 'info',
            self::OrderCreated        => 'success',
            self::OrderUpdated        => 'info',
            self::ProjectMilestone    => 'warning',
            self::ProductSubmitted    => 'info',
            self::ProductApproved     => 'success',
            self::ProductRejected     => 'warning',
            self::General             => 'info',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

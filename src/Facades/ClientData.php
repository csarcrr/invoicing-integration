<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject
 *
 * @method static self id(int $id)
 * @method static int|null getId()
 * @method static self name(string $name)
 * @method static string|null getName()
 * @method static self vat(string $vat)
 * @method static string|null getVat()
 * @method static self email(string $email)
 * @method static string|null getEmail()
 * @method static self phone(string $phone)
 * @method static string|null getPhone()
 * @method static self address(string $address)
 * @method static string|null getAddress()
 * @method static self city(string $city)
 * @method static string|null getCity()
 * @method static self postalCode(string $postalCode)
 * @method static string|null getPostalCode()
 * @method static self country(string $country)
 * @method static string|null getCountry()
 * @method static self notes(string $notes)
 * @method static string|null getNotes()
 * @method static self irsRetention(bool $irsRetention)
 * @method static bool|null getIrsRetention()
 * @method static self emailNotification(bool $emailNotification)
 * @method static bool|null getEmailNotification()
 * @method static self defaultPayDue(int $defaultPayDue)
 * @method static int|null getDefaultPayDue()
 *
 * @see \CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject
 */
class ClientData extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ClientDataObject::class;
    }
}

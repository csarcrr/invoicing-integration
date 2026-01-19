<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Facades;

use CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \CsarCrr\InvoicingIntegration\ValueObjects\ClientDataObject
 *
 * @method static ClientDataObject id(int $id)
 * @method static int|null getId()
 * @method static ClientDataObject name(string $name)
 * @method static string|null getName()
 * @method static ClientDataObject vat(string $vat)
 * @method static string|null getVat()
 * @method static ClientDataObject email(string $email)
 * @method static string|null getEmail()
 * @method static ClientDataObject phone(string $phone)
 * @method static string|null getPhone()
 * @method static ClientDataObject address(string $address)
 * @method static string|null getAddress()
 * @method static ClientDataObject city(string $city)
 * @method static string|null getCity()
 * @method static ClientDataObject postalCode(string $postalCode)
 * @method static string|null getPostalCode()
 * @method static ClientDataObject country(string $country)
 * @method static string|null getCountry()
 * @method static ClientDataObject notes(string $notes)
 * @method static string|null getNotes()
 * @method static ClientDataObject irsRetention(bool $irsRetention)
 * @method static bool|null getIrsRetention()
 * @method static ClientDataObject emailNotification(bool $emailNotification)
 * @method static bool|null getEmailNotification()
 * @method static ClientDataObject defaultPayDue(int $defaultPayDue)
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

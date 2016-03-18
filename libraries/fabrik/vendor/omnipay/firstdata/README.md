# Omnipay: First Data

**First Data driver for the Omnipay PHP payment processing library**

[![Build Status](https://travis-ci.org/thephpleague/omnipay-firstdata.png?branch=master)](https://travis-ci.org/thephpleague/omnipay-firstdata)
[![Latest Stable Version](https://poser.pugx.org/omnipay/firstdata/version.png)](https://packagist.org/packages/omnipay/firstdata)
[![Total Downloads](https://poser.pugx.org/omnipay/firstdata/d/total.png)](https://packagist.org/packages/omnipay/firstdata)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements First Data support for Omnipay.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "omnipay/firstdata": "~2.0"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## Basic Usage

The following gateways are provided by this package:

* FirstData_Connect
* FirstData_Webservice
* FirstData_Payeezy

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

## References

First Data Corporation is a global payment technology solutions company headquartered in Atlanta, Georgia,
United States.  First Data Corporation was incorporated in 1971. In 1980, American Express Information
Services Corporation (ISC) bought 80% of First Data.  First Data Corporation spun off from American Express
and went public in 1992.

The First Data Global Gateway Connect 2.0 is a simple payment solution for connecting an online store to
the First Data Global Gateway.  It provides redirect based payments (purchase() method with a corresponding
completePurchase() method).  It is referred to here as the "First Data Connect" gateway, currently at
version 2.0.

The Global Gateway was originally called the LinkPoint Gateway but since First Data's acquisition of
LinkPoint it is now known as the First Data Global Gateway. As of this writing the Global Gateway version
9.0 is supported. It is referred to here as the "First Data Webservice" gateway, more correctly speaking
it is the "First Data Global Web Services API", currently at version 9.0

The First Data Global Gateway e4 (previously referred to as "First Data Global", and so if you see
internet references to the First Data Global Gateway, they are probably referring to this one, distinguished
by having URLs like "api.globalgatewaye4.firstdata.com") is now called the Payeezy Gateway and is
referred to here as the "First Data Payeezy" Gateway.

The Connect, Global, and Payeezy gateways are implemented here although each have gone through a number
of API changes since their initial releases.

The First Data APIs are listed here:

https://www.firstdata.com/en_us/customer-center/merchants/first-data-global-gateway-api-software-landing.html

### First Data Connect 2.0

The First Data Connect 2.0 Integration guide is here:

https://www.firstdata.com/downloads/pdf/FDGG_Connect_2.0_Integration_Manual_v2.0.pdf

### First Data Global Web Services API 9.0

The Global Webservice API description is here:

https://www.firstdata.com/downloads/pdf/FDGG_Web_Service_API_v9.0.pdf

The API manual for an older (v1.1) version of the same can be found here:

https://www.firstdata.com/downloads/marketing-merchant/fd_globalgatewayapi_usermanual.pdf

Reference code that implements connections to this gateway can be found at:

* http://ashokks.com/First-Data-Global-Gateway-Web-Service-API-Complete-PHP-Example

# First Data Payeezy Gateway

API details for the Payeezy gateway are here:

https://support.payeezy.com/hc/en-us

and here:

https://support.payeezy.com/hc/en-us/articles/204029989-First-Data-Payeezy-Gateway-Web-Service-API-Reference-Guide-

Reference code that implements connections to this gateway can be found at:

* https://github.com/VinceG/php-first-data-api
* https://github.com/loganhenson/firstdata

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/thephpleague/omnipay-firstdata/issues),
or better yet, fork the library and submit a pull request.

# Magento 2 Coupon Code API

This module adds three API endpoints that provide methods for generating coupon codes for an existing Magento 2 Cart Price Rule from an external source. It has been tested with the REST API, although it should work equally well with the SOAP/XML API as well. Usage documentation is for REST usage only.

## Why?

Well, although Magento 2 provides a built-in method for generating a list of coupon codes for a promotional rule, in order to use these with another system (like a marketing email platform, or for contest systems, or even CRM systems like SalesForce), you have to generate a set number of coupon codes, and then export them via CSV. For on-demand generation, it makes more sense to generate codes as they're needed.

## Installation

Installation is available via composer. The package name is bangerkuwranger/magento-2-coupon-code-api. Just run these commands at your Magento root:
`composer require bangerkuwranger/magento-2-coupon-code-api`
`php bin/magento module:enable Bangerkuwranger_Couponcodeapi`
`php bin/magento setup:upgrade`

## Usage

Make sure to familiarize yourself with the [Magento API](http://devdocs.magento.com/guides/v2.1/get-started/bk-get-started-api.html) system first.

In order to use the endpoints properly, you'll need to create a [Cart Price Rule](http://docs.magento.com/m2/ee/user_guide/marketing/price-rules-cart.html) that has the Coupon field set to Auto. The other settings are up to you. Make sure that your rule is working on your system before setting up the coupon generation logic. 

This module allows you to check to make sure that a Magento customer can actually use the generated code by including a customer id when generating a new code. Magento will see which customer group the customer belongs to, and then verify that that group is allowed in your Cart Price Rule. It's optional, but useful if you are generating codes for specific customers. Setting the customer id parameter to zero will just generate the code without checking.

Anyhow, once installed, there will be three endpoints available:

1. GET /V1/bangerkuwranger/couponcode/getCartRule/
2. POST /V1/bangerkuwranger/couponcode/getCustIdByEmail/
3. POST /V1/bangerkuwranger/couponcode/getCouponCode/

The third one is the only really necessary endpoint; it's what actually generates the code for your Cart Price Rule. The other two are useful if you want to verify the Cart Rule's ID and the customer ID. All three methods require authentication of some sort; make sure to familiarize yourself with the [Magento API Authentication process](http://devdocs.magento.com/guides/v2.1/get-started/authentication/gs-authentication.html) so your logic incorporates the proper authentication steps. Once that's take care of, you can generate the code by making an API request to the getCouponCode endpoint, and you'll get a new coupon code back.

## Recommended Logic Flows

### Without Customer Group Validation

1. Perform authentication to Magento API
2. Request information about the Cart Rule from the API (getCartRule).
3. Verify that Cart Rule details match as expected.
4. Create a new Coupon Code for the Cart Rule using ruleId and setting custId to 0. (getCouponCode)

### With Customer Group Authentication

1. Perform authentication to Magento API
2. Request information about the Cart Rule from the API (getCartRule).
3. Verify that Cart Rule details match as expected.
4. Get custId and verify cust exists for email address using getCustIdByEmail endpoint.
5. If cust exists, continue. If not, you can create customer using Magento's built in API methods if that makes sense for your workflow.
6. Create a new Coupon Code for the Cart Rule using ruleId and custId (getCouponCode)

# Endpoint Documentation

## /bangerkuwranger/couponcode/getCartRule/

### Description

Given a cart rule id, this method returns an array of values from the cart rule if the rule exists, or an error if it does not exist or something else went wrong with the API transaction.

### Example

`curl -X GET "https://magento.host/index.php/rest/V1//bangerkuwranger/couponcode/getCartRule/?ruleId=20" -H “Content-Type: application/json” -H "Authorization: Bearer vbnf3hjklp5iuytre" `

### Method

GET

### Request *Query String* Parameters

`int $ruleId unique identifier for magento cart discount rule`

### Successful Response

#### Format

	[
		[0] (string): name,
		[1] (string): description,
		[2] (string): fromDate,
		[3] (string): toDate,
		[4] (int): usesPerCust,
		[5] (int): usesPerCoupon,
		[6] (bool): isActive,
		[7] (array):
			groupIds [
				(string) groupId,
				...additional groupIds...
			]
	]

### HTTP 400 Response

#### Reason

Bad Request

#### Format

	error-response {
		message (string): Error message ,
		errors (error-errors, optional),
		code (integer, optional): Error code ,
		parameters (error-parameters, optional),
		trace (string, optional): Stack trace
	}
	error-errors [
		error-errors-item
	]
	error-parameters [
		error-parameters-item
	]
	error-errors-item {
		message (string, optional): Error message ,
		parameters (error-parameters, optional)
	}
	error-parameters-item {
		resources (string, optional): ACL resource ,
		fieldName (string, optional): Missing or invalid field name ,
		fieldValue (string, optional): Incorrect field value
	}

### HTTP 401 Response

#### Reason

Not Authorized / Invalid Token

#### Format

	error-response {
		message (string): Error message ,
		errors (error-errors, optional),
		code (integer, optional): Error code ,
		parameters (error-parameters, optional),
		trace (string, optional): Stack trace
	}
	error-errors [
		error-errors-item
	]
	error-parameters [
		error-parameters-item
	]
	error-errors-item {
		message (string, optional): Error message ,
		parameters (error-parameters, optional)
	}
	error-parameters-item {
		resources (string, optional): ACL resource ,
		fieldName (string, optional): Missing or invalid field name ,
		fieldValue (string, optional): Incorrect field value
	}

### HTTP 500 Response

#### Reason

Server Error / Local Exception

#### Format

	error-response {
		message (string): Error message ,
		errors (error-errors, optional),
		code (integer, optional): Error code ,
		parameters (error-parameters, optional),
		trace (string, optional): Stack trace
	}
	error-errors [
		error-errors-item
	]
	error-parameters [
		error-parameters-item
	]
	error-errors-item {
		message (string, optional): Error message ,
		parameters (error-parameters, optional)
	}
	error-parameters-item {
		resources (string, optional): ACL resource ,
		fieldName (string, optional): Missing or invalid field name ,
		fieldValue (string, optional): Incorrect field value
	}

## /bangerkuwranger/couponcode/getCustIdByEmail/

### Description

Given an email address, this method returns a unique identifier for the magento customer, or an error if it does not exist or something else went wrong with the API transaction.

### Example

`curl -X POST "https://magento.host/index.php/rest/V1//bangerkuwranger/couponcode/getCustIdByEmail/" -H “Content-Type: application/json” -H "Authorization: Bearer vbnf3hjklp5iuytre" -d '{"email":"customer1@example.com"}'`

### Method

POST

### Request *Body* Parameters

`string $email email address to search for customer with`

### Successful Response

#### Format

`(string) “custId”`

### HTTP 400 Response

#### Reason

Bad Request

#### Format

	error-response {
		message (string): Error message ,
		errors (error-errors, optional),
		code (integer, optional): Error code ,
		parameters (error-parameters, optional),
		trace (string, optional): Stack trace
	}
	error-errors [
		error-errors-item
	]
	error-parameters [
		error-parameters-item
	]
	error-errors-item {
		message (string, optional): Error message ,
		parameters (error-parameters, optional)
	}
	error-parameters-item {
		resources (string, optional): ACL resource ,
		fieldName (string, optional): Missing or invalid field name ,
		fieldValue (string, optional): Incorrect field value
	}

### HTTP 401 Response

#### Reason

Not Authorized / Invalid Token

#### Format

	error-response {
		message (string): Error message ,
		errors (error-errors, optional),
		code (integer, optional): Error code ,
		parameters (error-parameters, optional),
		trace (string, optional): Stack trace
	}
	error-errors [
		error-errors-item
	]
	error-parameters [
		error-parameters-item
	]
	error-errors-item {
		message (string, optional): Error message ,
		parameters (error-parameters, optional)
	}
	error-parameters-item {
		resources (string, optional): ACL resource ,
		fieldName (string, optional): Missing or invalid field name ,
		fieldValue (string, optional): Incorrect field value
	}

### HTTP 500 Response

#### Reason

Server Error / Local Exception

#### Format

	error-response {
		message (string): Error message ,
		errors (error-errors, optional),
		code (integer, optional): Error code ,
		parameters (error-parameters, optional),
		trace (string, optional): Stack trace
	}
	error-errors [
		error-errors-item
	]
	error-parameters [
		error-parameters-item
	]
	error-errors-item {
		message (string, optional): Error message ,
		parameters (error-parameters, optional)
	}
	error-parameters-item {
		resources (string, optional): ACL resource ,
		fieldName (string, optional): Missing or invalid field name ,
		fieldValue (string, optional): Incorrect field value
	}

## /bangerkuwranger/couponcode/getCouponCode/

### Description

Given a cart rule id and customer id (use the number zero if customer id is not being used), this method returns a unique coupon code for the magento cart rule, or an error if it does not exist or something else went wrong with the API transaction. If customer id is being used, this will check if customer belongs to a group that can use the coupon code first, returning an error if customer is not authorized.

### Example

`curl -X POST "https://magento.host/index.php/rest/V1//bangerkuwranger/couponcode/getCouponCode/" -H “Content-Type: application/json” -H "Authorization: Bearer vbnf3hjklp5iuytre" -d '{"ruleId":31,”custId”:1055}'`

### Method

POST

### Request *Body* Parameters

`int $ruleId unique identifier for magento cart discount rule`
`int $custId unique identifier for magento customer`


### Successful Response

#### Format

`(string) “couponCode”`

### HTTP 400 Response

#### Reason

Bad Request

#### Format

	error-response {
		message (string): Error message ,
		errors (error-errors, optional),
		code (integer, optional): Error code ,
		parameters (error-parameters, optional),
		trace (string, optional): Stack trace
	}
	error-errors [
		error-errors-item
	]
	error-parameters [
		error-parameters-item
	]
	error-errors-item {
		message (string, optional): Error message ,
		parameters (error-parameters, optional)
	}
	error-parameters-item {
		resources (string, optional): ACL resource ,
		fieldName (string, optional): Missing or invalid field name ,
		fieldValue (string, optional): Incorrect field value
	}

### HTTP 401 Response

#### Reason

Not Authorized / Invalid Token

#### Format

	error-response {
		message (string): Error message ,
		errors (error-errors, optional),
		code (integer, optional): Error code ,
		parameters (error-parameters, optional),
		trace (string, optional): Stack trace
	}
	error-errors [
		error-errors-item
	]
	error-parameters [
		error-parameters-item
	]
	error-errors-item {
		message (string, optional): Error message ,
		parameters (error-parameters, optional)
	}
	error-parameters-item {
		resources (string, optional): ACL resource ,
		fieldName (string, optional): Missing or invalid field name ,
		fieldValue (string, optional): Incorrect field value
	}

### HTTP 500 Response

#### Reason

Server Error / Local Exception

#### Format

	error-response {
		message (string): Error message ,
		errors (error-errors, optional),
		code (integer, optional): Error code ,
		parameters (error-parameters, optional),
		trace (string, optional): Stack trace
	}
	error-errors [
		error-errors-item
	]
	error-parameters [
		error-parameters-item
	]
	error-errors-item {
		message (string, optional): Error message ,
		parameters (error-parameters, optional)
	}
	error-parameters-item {
		resources (string, optional): ACL resource ,
		fieldName (string, optional): Missing or invalid field name ,
		fieldValue (string, optional): Incorrect field value
	}

## A Web Template

### Features

- [A Web Template](#a-web-template)
  - [Features](#features)
  - [Register](#register)
    - [Headers](#headers)
    - [Parameters](#parameters)
    - [Response](#response)
  - [Login](#login)
    - [Headers](#headers-1)
    - [Parameters](#parameters-1)
    - [Response](#response-1)
  - [Send Email Verification OTP](#send-email-verification-otp)
    - [Headers](#headers-2)
    - [Response](#response-2)
  - [Email Verification](#email-verification)
    - [Headers](#headers-3)
    - [Parameters](#parameters-2)
    - [Response](#response-3)
  - [Forgot Password](#forgot-password)
    - [Headers](#headers-4)
    - [Parameters](#parameters-3)
    - [Response](#response-4)
  - [Reset Password](#reset-password)
    - [Headers](#headers-5)
    - [Parameters](#parameters-4)
    - [Response](#response-5)
  - [Fetch User Profile](#fetch-user-profile)
    - [Headers](#headers-6)
    - [Response](#response-6)
  - [Update User Profile](#update-user-profile)
    - [Headers](#headers-7)
    - [Parameters](#parameters-5)
    - [Response](#response-7)
  - [Deactivate Account](#deactivate-account)
    - [Headers](#headers-8)
    - [Response](#response-8)
  - [Request for Account Activation](#request-for-account-activation)
    - [Headers](#headers-9)
    - [Parameters](#parameters-6)
    - [Response](#response-9)
  - [Activate Account](#activate-account)
    - [Parameters](#parameters-7)
    - [Headers](#headers-10)
    - [Response](#response-10)
  - [Request For Account Delete](#request-for-account-delete)
    - [Headers](#headers-11)
    - [Response](#response-11)
  - [Delete Account](#delete-account)
    - [Parameters](#parameters-8)
    - [Headers](#headers-12)
    - [Response](#response-12)
  - [Get All Products](#get-all-products)
    - [Headers](#headers-13)
    - [Response](#response-13)
  - [Get a Specific Product](#get-a-specific-product)
    - [Headers](#headers-14)
    - [Response](#response-14)
  - [Get All Categories](#get-all-categories)
    - [Headers](#headers-15)
    - [Response](#response-15)
  - [Get a Specific Category](#get-a-specific-category)
    - [Headers](#headers-16)
    - [Response](#response-16)
  - [Get All Subcategories](#get-all-subcategories)
    - [Headers](#headers-17)
    - [Response](#response-17)
  - [Get a Specific Subcategory](#get-a-specific-subcategory)
    - [Headers](#headers-18)
    - [Response](#response-18)
  - [Get Order Summary](#get-order-summary)
    - [Headers](#headers-19)
    - [Parameters](#parameters-9)
    - [Products Example](#products-example)
    - [Response](#response-19)
  - [Place Order](#place-order)
    - [Headers](#headers-20)
    - [Parameters](#parameters-10)
    - [Request Example](#request-example)
    - [Response](#response-20)

### Register

_This Registers a User_

**URL:** /api/v1/register  
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_

#### Parameters

| Parameter     | Type   | Description |
| ------------- | ------ | ----------- |
| username      | string | _required_  |
| email         | string | _required_  |
| password      | string | _required_  |
| first_name    | string | _required_  |
| last_name     | string | _required_  |
| mobile_number | string | _required_  |

#### Response

> Success - 201 CREATED

```json
{
  "status": true,
  "message": "Account Created",
  "data": {
    "email": "jane@gmail.com",
    "username": "jane123",
    "first_name": "Jane",
    "last_name": "Doe",
    "mobile_number": "1234567890",
    "email_verified": false,
    "profile_picture": null,
    "bearer_token": "16|RmkkR0aLTBIgcWqHo9aDTqlDHwar7NvIvg58fq0I"
  }
}
```

> Error Responses

| Code | Message                 | Description              |
| ---- | ----------------------- | ------------------------ |
| 422  | Email Already Exists    | _if the email exists_    |
| 422  | Username Already Exists | _if the Username exists_ |
| 500  | Internal Server Error   | --                       |

[Back to Top](#features)

### Login

_This Logs a User in_

**URL:** /api/v1/login  
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_

#### Parameters

| Parameter            | Type   | Description |
| -------------------- | ------ | ----------- |
| email _or_ user_name | string | _required_  |
| password             | string | _required_  |

#### Response

```json
{
  "status": true,
  "message": "User Logged in Successfully",
  "data": {
    "email": "jane@gmail.com",
    "username": "jane123",
    "first_name": "Jane",
    "last_name": "Doe",
    "mobile_number": "1234567890",
    "email_verified": false,
    "profile_picture": null,
    "bearer_token": "16|RmkkR0aLTBIgcWqHo9aDTqlDHwar7NvIvg58fq0I"
  }
}
```

[Back to Top](#features)

### Send Email Verification OTP

_This sends an OTP to the user email for verification_

**URL:** /api/v1/email/otp  
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Response

```json
{
  "status": true,
  "message": "Email Verification OTP Sent",
  "data": null
}
```

[Back to Top](#features)

### Email Verification

_This Verifies a user email_

**URL:** /api/v1/email/verify  
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Parameters

| Parameter | Type   | Description |
| --------- | ------ | ----------- |
| otp       | string | _required_  |

#### Response

```json
{
  "status": true,
  "message": "Email Successfully Verified",
  "data": null
}
```

[Back to Top](#features)

### Forgot Password

_This sends a password reset link to a user_

**URL:** /api/v1/forgot-password
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Parameters

| Parameter | Type   | Description |
| --------- | ------ | ----------- |
| email     | string | _required_  |

#### Response

```json
{
  "status": true,
  "message": "Password Reset Link Sent to Your Email",
  "data": null
}
```

[Back to Top](#features)

### Reset Password

_This resets a user password_

**URL:** /api/v1/reset-password  
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_

#### Parameters

| Parameter    | Type   | Description |
| ------------ | ------ | ----------- |
| old_password | string | _required_  |
| new_password | string | _required_  |

#### Response

```json
{
  "status": true,
  "message": "Password Reset Successfull",
  "data": null
}
```

[Back to Top](#features)

### Fetch User Profile

_This returns a user profile details_

**URL:** /api/v1/user/profile  
**Method:** GET

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Response

```json
{
  "status": true,
  "message": "User Profile Retrieved",
  "data": {
    "email": "jane@gmail.com",
    "username": "jane123",
    "first_name": "Jane",
    "last_name": "Doe",
    "mobile_number": "1234567890",
    "email_verified": false,
    "profile_picture": null
  }
}
```

[Back to Top](#features)

### Update User Profile

_This updates a user profile details_

**URL:** /api/v1/user/update-profile
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Parameters

| Parameter       |            | Description |
| --------------- | ---------- | ----------- |
| first_name      | _optional_ |
| last_name       | _optional_ |
| mobile_number   | _optional_ |
| profile_picture | _optional_ |

#### Response

```json
{
  "status": true,
  "message": "User Profile Updated",
  "data": {
    "email": "jane@gmail.com",
    "username": "jane123",
    "first_name": "Jane",
    "last_name": "Doe",
    "mobile_number": "1234567890",
    "email_verified": false,
    "profile_picture": null
  }
}
```

[Back to Top](#features)

### Deactivate Account

_This deactivates a user account_

**URL:** /api/v1/user/deactivate
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Response

```json
{
  "status": true,
  "message": "User Account Deactivated",
  "data": null
}
```

[Back to Top](#features)

### Request for Account Activation

_This sends an otp for account activation_

**URL:** /api/v1/user/request-activation
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_

#### Parameters

| Parameter |            | Description |
| --------- | ---------- | ----------- |
| email     | _required_ |

#### Response

```json
{
  "status": true,
  "message": "Account Activation OTP Sent",
  "data": null
}
```

[Back to Top](#features)

### Activate Account

_This sends an otp for account activation_

**URL:** /api/v1/user/activate
**Method:** POST

#### Parameters

| Parameter |            | Description |
| --------- | ---------- | ----------- |
| email     | _required_ |
| otp       | _required_ |

#### Headers

Content-Type: _application/json_  
Accept: _application/json_

#### Response

```json
{
  "status": true,
  "message": "User Account Activated",
  "data": null
}
```

[Back to Top](#features)

### Request For Account Delete

_This sends an otp for account deletion_

**URL:** /api/v1/user/request-delete
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Response

```json
{
  "status": true,
  "message": "Account Deletion OTP Sent",
  "data": null
}
```

[Back to Top](#features)

### Delete Account

_This deletes a user account_

**URL:** /api/v1/user/delete
**Method:** POST

#### Parameters

| Parameter |            | Description |
| --------- | ---------- | ----------- |
| otp       | _required_ |

#### Headers

Content-Type: _application/json_  
Accept: _application/json_

#### Response

```json
{
  "status": true,
  "message": "Account Deleted",
  "data": null
}
```

[Back to Top](#features)

### Get All Products

_Retrieve all products_

**URL:** /api/v1/product  
**Method:** GET

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Response

```json
{
  "status": true,
  "message": "",
  "data": [
    {
      "id": 2,
      "name": "Coffee",
      "slug": "coffee",
      "status": null,
      "is_active": 1,
      "selling_price": "700.00",
      "original_price": "750.00",
      "description": "A Coffee",
      "delivery_cost": "0.00",
      "quantity": 10022,
      "discount_percent": 0,
      "category": {
        "id": 2,
        "slug": "drinks",
        "name": "Drinks"
      },
      "sub_category": {
        "id": 1,
        "slug": "drinks",
        "name": "Drinks"
      },
      "images": ["/storage/uploads/products/images/12345.png"]
    }
  ]
}
```

[Back to Top](#features)

### Get a Specific Product

_Retrieve a specific product by ID_

**URL:** /api/v1/product/{id}  
**Method:** GET

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Response

```json
{
  "status": true,
  "message": "",
  "data": {
    "id": 2,
    "name": "Coffee",
    "slug": "coffee",
    "status": null,
    "is_active": 1,
    "selling_price": "700.00",
    "original_price": "750.00",
    "description": "A Coffee",
    "delivery_cost": "0.00",
    "quantity": 9911,
    "discount_percent": 0,
    "category": {
      "id": 2,
      "slug": "drinks",
      "name": "Drinks"
    },
    "sub_category": {
      "id": 1,
      "slug": "drinks",
      "name": "Drinks"
    },
    "images": ["/storage/uploads/products/images/12345.png"]
  }
}
```

[Back to Top](#features)

### Get All Categories

_Retrieve all categories_

**URL:** /api/v1/category  
**Method:** GET

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Response

```json
{
  "status": true,
  "message": "",
  "data": [
    {
      "id": 2,
      "name": "Drinks",
      "slug": "drinks",
      "image": "/storage/uploads/subcategory/75536jpg",
      "sub_categories": [
        {
          "name": "Drinks",
          "slug": "drinks",
          "image": "/storage/uploads/subcategory/79656jpg"
        }
      ]
    }
  ]
}
```

[Back to Top](#features)

### Get a Specific Category

_Retrieve a specific category by ID_

**URL:** /api/v1/category/{id}  
**Method:** GET

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Response

```json
{
  "status": true,
  "message": "",
  "data": {
    "id": 2,
    "name": "Drinks",
    "slug": "drinks",
    "image": "/storage/uploads/subcategory/75536jpg",
    "sub_categories": [
      {
        "name": "Drinks",
        "slug": "drinks",
        "image": "/storage/uploads/subcategory/79656jpg"
      }
    ]
  }
}
```

[Back to Top](#features)

### Get All Subcategories

_Retrieve all subcategories_

**URL:** /api/v1/subcategory  
**Method:** GET

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Response

```json
{
  "status": true,
  "message": "",
  "data": [
    {
      "id": 1,
      "name": "Drinks",
      "slug": "drinks",
      "image": "/storage/uploads/subcategory/79656jpg",
      "category": {
        "name": "Drinks",
        "slug": "drinks",
        "image": "/storage/uploads/subcategory/75536jpg"
      }
    }
  ]
}
```

[Back to Top](#features)

### Get a Specific Subcategory

_Retrieve a specific subcategory by ID_

**URL:** /api/v1/subcategory/{id}  
**Method:** GET

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Response

```json
{
  "status": true,
  "message": "",
  "data": {
    "id": 1,
    "name": "Drinks",
    "slug": "drinks",
    "image": "/storage/uploads/subcategory/79656jpg",
    "category": {
      "name": "Drinks",
      "slug": "drinks",
      "image": "/storage/uploads/subcategory/75536jpg"
    }
  }
}
```

[Back to Top](#features)

### Get Order Summary

_Retrieve an order summary_

**URL:** /api/v1/summary  
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Parameters

| Parameter | Type  | Description                                                          |
| --------- | ----- | -------------------------------------------------------------------- |
| products  | array | Array of product IDs and quantities in the format: `[{id:1, qty:2}]` |

#### Products Example

```json
[{ "id": 1, "qty": 2 }]
```

#### Response

```json
{
  "status": true,
  "message": "Order Summary",
  "data": {
    "error": false,
    "orderTotal": 7000,
    "orderSubTotal": 7000,
    "orderDiscount": 0,
    "products": [
      {
        "id": 2,
        "name": "Coffee",
        "slug": "coffee",
        "status": null,
        "is_active": 1,
        "selling_price": "700.00",
        "original_price": "750.00",
        "description": "A Coffee",
        "delivery_cost": "0.00",
        "quantity": 9911,
        "discount_percent": 0,
        "category": {
          "id": 2,
          "slug": "drinks",
          "name": "Drinks"
        },
        "sub_category": {
          "id": 1,
          "slug": "drinks",
          "name": "Drinks"
        },
        "images": ["/storage/uploads/products/images/12345.png"],
        "price": "700.00",
        "qty": "10"
      }
    ]
  }
}
```

[Back to Top](#features)

### Place Order

_Endpoint for placing an order_

**URL:** /api/v1/order  
**Method:** POST

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Parameters

| Parameter       | Type   | Description                                                   |
| --------------- | ------ | ------------------------------------------------------------- |
| products        | array  | Array of product IDs and quantities                           |
| delivery_method | string | Delivery method (`Pick-Up` , `Dispatch`)                      |
| payment_method  | string | Payment method (`Card`, `Bank / Transfer`, `Cash`, `Balance`) |

#### Request Example

```json
{
  "products": [
    { "id": 1, "qty": 2 },
    { "id": 2, "qty": 1 }
  ],
  "delivery_method": "Pick-Up",
  "payment_method": "Card"
}
```

#### Response

```json
{
  "status": true,
  "message": "",
  "data": [
    {
      "id": 95,
      "status": "Processing",
      "payment_status": "Paid",
      "reference": "75374825213",
      "payment_method": "Balance",
      "delivery_method": "Pick-Up",
      "delivery_cost": "0.00",
      "total": "1,400.00",
      "date": "12:24:47PM 04th Jan 2024",
      "sub_total": "1,400.00",
      "discount": "0.00",
      "shipping": "0.00",
      "due": "0.00",
      "paid": "1,400.00",
      "products": [
        {
          "id": 2,
          "name": "Coffee",
          "price": "700.00",
          "qty": 2,
          "discount": "0.00",
          "sub_total": "1400.00",
          "image": "/storage/uploads/products/images/12345.png"
        }
      ],
      "payments": [
        {
          "amount": "1,400.00",
          "reference": "68044162161",
          "method": "Balance",
          "date": "12:24:47pm 04th-Jan-2024",
          "paid_by": null
        }
      ]
    }
  ]
}
```

[Back to Top](#features)

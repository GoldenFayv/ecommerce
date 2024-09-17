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
  - [Fetch User Profile](#fetch-user-profile)
    - [Headers](#headers-2)
    - [Response](#response-2)
  - [Send Complaint](#send-complaint)
    - [Headers](#headers-3)
    - [Parameter](#parameter)
    - [Response](#response-3)

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

### Send Complaint

_This send a user's complaints_

**URL:** /api/v1/user/complain  
**Method:** post

#### Headers

Content-Type: _application/json_  
Accept: _application/json_  
Authorization: Bearer _bearer_token retrieved-from [Login](#login)_

#### Parameter

| Parameter | Type   | Description       |
| --------- | ------ | ----------------- |
| subject   | string | _required_        |
| message   | string | _required_        |
| images    | file   | _nullable, array_ |

#### Response

```json
{
    "status": true,
    "message": "The support team will follow using your email",
    "data": null
}
```

[Back to Top](#features)

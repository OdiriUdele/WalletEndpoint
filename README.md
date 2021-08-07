
## Wallet Endpoint and state/LGA import.

This is a laravel application that takes  input from different endpoints to enable the user carry out multiple actions from registration to login and down to state/LGA import.  Tools Used:

- Laravel Framework => ^7.29
- Laravel tymon/jwt-auth=> "^1.0. (For Authentication)
- Php => 7.4.15)
- maatwebsite/excel: ^3.1 (For Excel Import).

I made use of Requests,Resource, models, migrations, controller and services.

##Scope
- A User can sign up
- A user can perform authentication operations like resetting passwords and logging out.
- A user can create a wallet, update a wallet, view wallet information and delete a wallet.
- Scope also covers the following endpoints:
  An endpoint that;
- Gets all users in the system.
- Gets a user’s detail including the wallets they own and the transaction history of that user.
- Gets all wallets in the system.
- Gets a wallet’s detail including its owner, type and the transaction history of that wallet.
- Gets the count of users, count of wallets, total wallet balance, total volume of transactions.
- Sends money from one wallet to another.
- Trigger an import of the data in the excel file into the database
- Returns state information in format below:
 ```json
    {
    "response": {
        "status": "success",
        "responseCode": 200,
        "responseDescription": "States and LGAs"
    },
    "state": [
        {
            "FCT": [
                "Abaji",
                "Abuja Municipal",
                "Bwari",
                "Gwagwalada",
                "Kuje",
                "Kwali"
            ]
        },
        {
            "Aba": [
                "Aba North",
                "Aba South",
                "Umuahia North",
                "Umuahia South"
            ]
        },
        {
            "Akwa Ibom": [
                "Eket",
                "Uyo",
                "Calabar Municipality"
            ]
        }
    ]
}
```

## Some key endpoints
- api/v1/general/wallet-types [//fetch wallet types]
- api/v1/general/users [//fetch all users]
- api/v1/general/users/{insert_user_id} /[fetches a user with associatd information]
- api/v1/general/wallets   [//get all wallets]
- api/v1/general/wallets/{insert_wallet_id} [//get wallet with associated information]
- api/v1/general/detail-count [//get user copunt, wallet count, total wallet balance, transaction volume]
- api/v1/general/send-money  [//transfer money from one wallet to another]



## Setup

- Clone repo
- Run composer intall/update
- copy .env.example to .env
- Run Php artisan key:generate
- Run Php Artisan jwt:secret
- Run Php Artisan migrate.

## API Documentation
This application at the time of this writing has some endpoints for authentication, user operations (encompassing wallet operations) and  General endpoints. To view the parameter requirements and expected return values and check out the detailed documentation please visit the postman documenter url below.<br>
**[Api Documentation Url (https://documenter.getpostman.com/view/7533984/TzskDNrK) ](https://documenter.getpostman.com/view/7533984/TzskDNrK)**



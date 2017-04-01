# agile-dashboard
Dashboard with metrics for agile companies

## How to install

1. Download and install **[Composer](http://getcomposer.org/download)**.

2. Clone the repository:


    $ git clone https://github.com/rgies/agile-dashboard.git
    $ cd agile-dashboard


## How to configure

1. Start setup and follow the instuctions:

    $ composer install

2. Create database table

    $ app/console doctrine:database:create
    
3. Update database schema

    $ app/console doctrine:schema:update --force
    

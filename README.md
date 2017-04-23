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
    
## Develop own widgets

1. Create widget code skeleton

        $ app/console dashboard:generate:widget
        
2. Add generated widget name to config 

    _app/config/widget.yml_

        # Widget plugins
        widget_plugins:
            'MyWidgetBundle': 'My widget name'

3. Insert view and controller code

    _Resources/views/Default/index.html.twig_
    
        {% block widget_body %}
            <!-- ADD HERE YOUR WIDGET CONTENT -->
            ...
        {% endblock %}

    _Controller/DefaultController.php_
    
        // ======================================================
        // INSERT HERE YOUR CODE TO COLLECT THE NEEDED DATA
        // ======================================================
        $response = array(
            'value' => $insert_here_your_data
        );
        // ======================================================

4. Optional change data model

    Adapt entity file _Entity/WidgetConfig.php_
    Adapt form file _Form/WidgetConfigType.php_

5. Update database schema

		$ app/console doctrine:schema:update --force

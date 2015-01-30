#RocketShip PHP Framework

---

RocketShip is a web framework that makes developing web sites and web applications in record time. The documentation will be available soon.

### Why does the code say it's version 1.0.0? 
---
It is 1.0.0 under the name RocketShip, but it's actually the 8th version of this framework (only the name as changed).

### When should i use RocketShip
---
Basically anytime you need to code something without the framework getting in your way too much. If you need MySQL, don't ask, i'm not going to add it to RocketShip. RocketShip only supports [MongoDB](http://www.mongodb.org/).

### Documentation
---
For now the documentation is only available as API documentation. But for the sake of how you install and start with it, here is a short how-to.


1. Requirements
2. Install
3. Getting started
4. Routes
5. Controllers and Views
6. API
7. Database
8. Special Note


1. Requirements
---
RocketShip requires [Composer](https://getcomposer.org/), PHP 5.5 and up and that your php installation has the mongo and mcrypt extensions.

That's it! You can optionally have either APC, Redis or Memcached too.


2. Install
---
What you get in the package is all you need. Run composer like so:

    php ~/composer.phar install
    
In this example, composer is in the user's home directory.

After composer installs everything, you are ready to start working.


3. Getting started
---
From there, you can hit the your project's url in the browser. You should see **Rocketship is ready to launch**.

Now you can start generating stuff in just a few seconds. If you look in the `bin` folder, you have a script called `generator`. You run it like this:

    php generate --help
    
You will get what you can create with it. With this tool you can create the following:

1. Controller
2. Model
3. Bundle
4. Bundle Controller
5. Bundle Model
6. Command line script
7. Test class

This will generate everything you need. Quickly!

4. Routes
---

Creating routes can be done 2 ways. Either by your application's routes file or by a bundle's routes file. Bundles will be explain a little later on. Normally routes look like this

    default:
        uri:
            en: /en
            fr: /fr                  # supports (:any) (:param) and (:num)
        action: method@controller    # method@controller
        secure: no                   # https page?
        enforce: no                  # enforce locale rules
        api: yes                     # yes makes it available to the api engine
        verbs: [GET,POST]            # What verbs are allowed on this route (API)


You can define special patterns for routes to make the definition clearer. Like this

    patterns:
        userid: (:param)
        
    your_route:
        uri:
            en: /user/(userid)
            de: /utilisaeur/(userid)
        action: method@default
        secure: no
        enforce: no
        
        
(:param) = accept anything except `/` (by anything, this means any valid url character)<br/>
(:any)   = same as `(:param)` but accepts `/`<br/>
(:num)   = numeric value only

Here is a list of ways route URIs are accepted

    defined as: 
        /blog
    
    accepted as:
        /blog
        /blog/
        /blog.html
        /blog.json
 
 You can use it the way you prefer. But note that the .json ending will trigger the JSON view rendering.
 
 
5. Controllers and Views
---
In controllers, you can pretty much do whatever php code you want. You never need to include any model, bundle or core RocketShip libraries, it's all taken care of for you. Here is a short example of what you can do with controllers:

    <?php

    use RocketShip\Controller\Application;

    class DefaultController extends Application
    {
        public function index()
        {
            // Nothing to do
            // Layout is already loaded by default
            // View defaults to the method name (ex: views/default/index.html)
        }
        
        public function manualView()
        {
            // No need for the path or .html. 
            // The path is assumed to be views/controller_name
            $this->app->view->render('my-page');
        }
        
        public function setSomeValuesToView()
        {
            $this->app->view->set('key', 'value');
            
            // If you have multiple values to set (2 arrays)
            $this->app->view->batch(['key1', 'key2'], ['value1', 'value2']);
        }
        
        public function receivingDynamicRouteArguments($userid)
        {
            // Your route would be something like /user/(:param)
            
            // Your controller will be passed every dynamic section of your route
            // directly to the function as arguments
        }
        
        public function getPostData()
        {
            $var = $this->app->input->post('index', true); // true = xss cleaning
            
            // Or get everything and clean it all
            $post = $this->app->input->post('all', true);
            
            // Use false if you expect HTML or similar data     
            
            // use ->get or ->put for these types of user supplied data
            // for basic angular $http.post use ->angularPost       
        }
        
        public function renderJSONOnly()
        {
            // Set your view data like it was a normal page
            
            // No view, but tell it to output JSON
            $this->view->render(null, RocketShip\View::JSON);
        }
    }

The way you get your view data on the other side (in views):

    <?=$this->your_key_here;?>
    
Note that Controllers, Views Bundles and any of the classes that extend the `Base` class inherit of the $app property. This property is the "brain" of any RocketShip app. See special note to know what it does.

Also interesting note on Views, you have access to the HTML helper like this:

    $this->html
    
This is useful for css, javascript loading. Like this:

    <?=$this->html->js('js1', 'js2', 'js3');?>
    
This is expecting that your javascript files reside in `/public/app/javascript/`. The same applies for stylesheets and images. Images are loaded like this:

    <img src="<?=$this->html->image('yourimage.png');?>">
   
####Layouts

In RocketShip, there is this concept of layouts. Layouts are files that contain all of the "recurring" HTML for your pages (header, sidebar, footer, etc..). By default the `default` layout is used. You can create whatever you want. To make the view appear in a layout use the following HTML comment

    <!-- view -->
   
Take a look at the default controller to see exactly how it works. Within the controller you can use a layout with this method:

    $this->view->useLayout('layout_you_want_without_html');
    
You can also specify that the layout you want is within a specific bundle, like this:

    $this->view->useLayout('layout_name@bundle_name');


6. API
---
Built-in is an API server. This enables you to create API apps without having to write a bunch of code to enable it and handling security. The internal API engine supports OAuth2 authentication and also has support for scopes.

You can easily create users for the api using the `createAccess` method in the API class. For example:

    $api    = RocketShip\Api;
    $access = $api->createAccess('access_redirect_uri');

This method returns user api key and user secret. If you want to validate that the API client has the permission you require, you can test for it like this:

    $this->app->api->validatePermission('required_scope_permission');    


7. Database
---
Here is how the database api works.

    // Create an index on a field (two ways)
    User::index('field', 1/-1);
    $user->index('field', 1/-1);
    
    // Create a compound index of many fields (two ways)
    User::compound(array('field' => 1/-1, 'field2' => 1/-1));
    $user->compound(array('field' => 1/-1', 'field2' => 1/-1));

    // Get a record by it's id (two ways)
    $record = $user->findById(...);
    $record = User::withId(...)
    
    // Get a record with a query
    $record = $user->where(array(...))->order('field ASC')->find();
    
    // Get all record that match query
    $records = $user->where(array(...))->order('field ASC)->findAll();

    // Get all and paginate (page 1, 20 results per page)
    $records = $user->where(array(...))->paginate(1, 20)->findAll();

    // Get a reference from a record (reference_field needs to be a valid reference)
    $user->getReference($obj->reference_field);
    
    // Create a reference when creating a record (create a reference to a user)
    $record->field_name = User::reference(..id..);

    // Add a record
    $user->field = 'value';
    $user->save();
    
    // Update a record based on it's id
    $user->field_to_update = 'new value';
    $user->_id             = $id;
    $user->save();
    
    // Update a record based on a specific field
    $user->field_to_update = 'new value';
    $user->fielda          = 1;
    $user->save('fielda');
    
    // Add a file to GridFS (private $isGridFS = true must be set in your model)
    $model->your_meta = '...';
    $model->whatever_you_want = '..';
    $model->addFile('/path/to/file', true, false);         // local file
    $model->addFile($binary_data, false, false);           // binary data
    $model->addFile('_FILES_uploaded_index', true, true);  // uploaded file
    
    // Get a file by a query
    $file = $model->where(array('my_meta' => 'hello'))->getFile();    
    
    // Get a file by it's id
    $file = $model->getFileById('...');
    
    // Destroy a/many file(s) by a query
    $model->where(array('meta' => '..'))->destroyFiles(true/false) // true = limit to 1 (default)
    
    // Destroy a file by it's id
    $model->destroyFileById('...');

If you feel lazy or feel you do not need a model class for something, you can use what we call `QuickCall` like so:

    $temporary_model = new RocketShip\Database\Collection('the_collection_you_want');
    ...manipulations...


8. Special note
---
About the $app property. Here is what it contains that you can use within all the classes that extends the `Base` class. (Including controllers, models, bundles)

- events      (Event and filter manager)
- session     (Session manager)
- config      (App configurations)
- constants   (App constants)
- environment (Current environment)
- site_url    (Full site url)
- url_path    (The path of the url from the document root, if document root is not `/`)
- root_path   (The root path, ex: `/var/www/yoursite/your-project`)
- uri         (The current URI)
- domain      (The site's domain)
- route       (The current route configuration)
- alternate_routes (All the route uri's for the other languages for the current route)
- bundles     (All the loaded bundle instances)
- helpers     (All the loaded helper instances)
- upload      (The upload manager)
- locale      (The locale manager)
- input       (The input filtration system)
- router      (The routing engine)
- api         (The api engine)

So pretty much everything that is really useful :)

Also the `tools` and `bin` directories should be deleted before moving to production.

**API Documentation and More detailled documentation coming soon.**
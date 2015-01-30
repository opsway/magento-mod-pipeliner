<?php
$description =<<<DESC
Why Pipeliner CRM? Because Seeing Is Believing
<br /><br />
Visualize your sales pipeline and gain actionable insights. Open your Pipeliner CRM and get an instantly readable graphical overview of all your opportunities in the context of your sales process. A visually rich, uncluttered model of your actual sales pipeline acts as your interface. It’s real-time sales data, organized so you can always remain focused on revenue targets.<br /><br />
Highlights:<br />

    Gain insights fast, and devise more effective sales strategies<br />
    Focus on proactive coaching support (instead of jumping in as a “super closer”)<br />
    Click and voila!—Instantly “readable” sales and activity reports<br />
    Promote best practices with popular and easily understood visual tools<br />
    Search less and sell more—Customer and account details are in one comprehensive view<br /><br />

Because Productivity Soars 24/7 (Online or Off)<br /><br />

Your Pipeliner CRM system is available anytime, anywhere in the world. We use a unique hybrid approach—a blend of Cloud-based and on-premise engineering—we call it the SMART Cloud. Your data is always safe in the Cloud, but you are always able to work with your entire system anywhere.<br /><br />
Highlights:
<br />
    Work on your own schedule, online or offline<br />
    Most recent synced version of your system is always ready<br />
    Control your schedule when on the road, at “no access” customer locations, on a hike, or on a plane<br /><br />

Smart Cloud CRM<br />
Because You Can Track Progress in Real Time
<br /><br />
Pipeliner CRM for sales teams makes it easier to visualize and follow your sales process.<br />
Highlights:<br /><br />

    1-to-Many-With-Any Architecture associates one Contact to Multiple Accounts, providing context for your complex and interconnected relationships, as they evolve.
    Full-Featured Product Catalog is a massive time saver. Customers can apply discounts, taxes, and automatically calculate fields -- tying the sales process more intimately to workflow.<br /><br />
    Forecasts are front and center, updated in real time<br />
    View pipeline from any angle at any moment<br />
    Notifications let users and admins set email notifications across most every Activity stream, facilitating better cross-company awareness to what matters.<br />
    Our Advanced Calendar syncs with Google and Outlook! There’s also an agenda view, activity extract, and support for 5 different layouts.<br />
    Save your favorite views as Profiles and see them with a click<br />
    Alerts, timeline views, and powerful filters improve sales forecast accuracy and lower risk<br />
    Collaboration tools for sharing, delegating, and interacting keep salespeople working toward goals in a timely way<br />
    Social monitoring adds relevance to prospect relationships and builds trust<br /><br />

Social CRM Feed<br />
Because Common Sense Pricing Has Big Advantages
<br /><br />
Pipeliner is affordable, easy to implement, and has a high rate of adoption by salespeople and teams.<br /><br />
Highlights:<br />

    One low price for all the core features, plug-ins, offline client, mobile CRM app<br />
    Full implementation in hours<br />
    Full support as you come on board<br />
    Sales teams see value and adopt enthusiastically<br />
    Training in hours with low learning curve<br />
    Low monthly fee and no hidden costs<br />
    30-day trial with no strings attached, no credit card required<br />
DESC;

return array(

//The base_dir and archive_file path are combined to point to your tar archive
//The basic idea is a seperate process builds the tar file, then this finds it
'base_dir'               => './',
'archive_files'          => 'vendor/opsway/magento-pipeliner/src',

//The Magento Connect extension name.  Must be unique on Magento Connect
//Has no relation to your code module name.  Will be the Connect extension name
'extension_name'         => 'opswaypipeliner',

//Your extension version.  By default, if you're creating an extension from a 
//single Magento module, the tar-to-connect script will look to make sure this
//matches the module version.  You can skip this check by setting the 
//skip_version_compare value to true
'extension_version'      => '1.0.0',
'skip_version_compare'   => false,

//You can also have the package script use the version in the module you 
//are packaging with. 
'auto_detect_version'   => true,

//Where on your local system you'd like to build the files to
'path_output'            => './var/build-connect',

//Magento Connect license value. 
'stability'              => 'stable',

//Magento Connect license value 
'license'                => 'GPL',

//Magento Connect channel value.  This should almost always (always?) be community
'channel'                => 'community',

//Magento Connect information fields.
'summary'                => 'Extension allow synchronizing data bidirectional between Magento stores and PipelinerCRM system. ',
'description'            => $description,
'notes'                  => 'First release.',

//Magento Connect author information. If author_email is foo@example.com, script will
//prompt you for the correct name.  Should match your http://www.magentocommerce.com/
//login email address
'author_name'        => 'OpsWay',
'author_user'        => 'opsway',
'author_email'       => 'support@opsway.com',

// Optional: adds additional author nodes to package.xml
'additional_authors'     => array(),

//PHP min/max fields for Connect.  I don't know if anyone uses these, but you should
//probably check that they're accurate
'php_min'                => '5.2.0',
'php_max'                => '6.0.0',

//PHP extension dependencies. An array containing one or more of either:
//  - a single string (the name of the extension dependency); use this if the
//    extension version does not matter
//  - an associative array with 'name', 'min', and 'max' keys which correspond
//    to the extension's name and min/max required versions
//Example:
//    array('json', array('name' => 'mongo', 'min' => '1.3.0', 'max' => '1.4.0'))
'extensions'             => array()
);

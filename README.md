EActiveRecordAx
===============

Author:  Cristian Salazar H.  <christiansalazarh@gmail.com>

Repo:  [https://github.com/christiansalazar/yiiattributex](https://github.com/christiansalazar/yiiattributex "https://github.com/christiansalazar/yiiattributex")

**Allows a CActiveRecord based class to use extended attributes stored in a blob.**

Using this specialized class you can deal with extra attributes
not declared in the table scheme, instead, stored in a blob, as follows:

~~~
[php]

// Color.php, defined as: class Color extends EActiveRecordEx { ... }
$red = new Color;
$red->name = 'red';
$red->value = '#f00';
// extended attributes, will be stored in a single blob
$red->rgb_notation = 'rgb(255,0,0)';
$red->label = 'This is a red color';
$red->insert(); // inserts a new record, the two extra fields are stored in
		// one single database column named: ax_data (see also seetings)
~~~

In this example the extra attributes: **'rgb_notation' and 'label' does not exists in the table schema**, this both attributes are treated as regular model attributes with no difference as others persisted in table columns.

In order to setup this extension **you're required to create a new table column in your schema (typically a BLOB column)**, give it a name (by default is named: 'ax_data') and declare it via settings in your config file (see also later).

## The Storage Column and Model Attributes

As an example, suppose you have this two extra attributes declared in your config file ('rgb_notation' and 'label'), also, a new column (the storage colum) was created in your table schema (name it 'ax_data'), so, future calls to $model->attributes will return an array containing all your regular table columns and class attributes, plus this two extra attributes also excluding the storage attribute (ax_data).

##Dealing with extra attributes

There is no difference at modeling level with the extra declared attributes,
you can use them in any CDataProvider based widget (CGridView, CListColumn,etc)
also, the regular usage.

	// the 'first_name' is not declared in the table schema, but will persist.
	$model->first_name = "Christian";
 	$model->save();

Also, this works too:

	$model = Person::model()->findByPk(123);
	echo "First Name is: ".$model->first_name;  // will echo "Christian"

The extra attribute is present when retriving a attribute list:

	$list = $model->attributes;   // this list will include 'first_name'

##Setup Instructions

1. git clone (or download) this extension into your protected/extensions dir.

		git clone https://github.com/christiansalazar/yiiattributex.git

2. edit your config/main.php settings file and put the imports path:

   		'import'=>array(
       		'application.models.*',
       		'application.components.*',
       		'application.extensions.yiiattributex.*',   // << HERE
       	),

3. Add a LONGBLOB attribute to your table scheme by using a command similar to:

		"alter table `color` add column `ax_data` longblob;

4. Define the extra attributes in your config/main.php settings file 

   		'params'=>array(
   			'yiiattributex' => array(
				// the class name
   				'Color'=>array(
					// this entry must go first.  ax_data is the attribute name
					// created in your scheme.
   						'settings' => array('ax_data'),
					// the extra fields:
   						'rgb_notation'=>array('#000',),
   						'label'=>array('no label yet',),
   				),
   			),
   		),

5. Extend your classes from EActiveRecordAx instead of CActiveRecord.


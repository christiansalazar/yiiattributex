EActiveRecordAx
===============

Author:  Cristian Salazar H.  <christiansalazarh@gmail.com>

**Allows a CActiveRecord based class to use extended attributes stored in a blob.**

In regular CActiveRecord based instances you have:

```
// Color.php, defined as: class Color extends CActiveRecord { ... }
$red = new Color;  
$red->name = 'red';  
$red->value='#f00'; 
$red->insert();
```

Now, using this specialized class you can deal with extra attributes
not declared in the table structure, instead, stored in a blob, as follows:

```
// Color.php, defined as: class Color extends EActiveRecordEx { ... }
// You can do the same operations as normal CActiveRecord, plus:
$red = new Color;
$red->name = 'red';
$red->value = '#f00';
// extended attributes, will be stored in a single blob
 $red->rgb_notation = 'rgb(255,0,0)';
 $red->label = 'This is a red color';
$red->insert();
```

*Please note the 'rgb_notation' and 'label' attributes does not exists in
the table schema. This fields are declared in a config file into your
Yii Framework Application.*

The mentioned sample fields shown above are declared in a config file, and,
are persisted in a single attribute previously defined in your scheme, in other
words, you are required to create a new attribute (LONGBLOB) in your table
scheme and pass it via settings, by default this field is called: 'ax_data'.


Setup Instructions
------------------

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


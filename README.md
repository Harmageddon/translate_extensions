# Translate Extensions

This program can be used to analyze the translations of extensions for the CMS **Joomla!**. It automatically checks for untranslated and unused language strings and allows extension developers to provide complete translation files.

## Installation

Download the repository contents and unpack it somewhere on your web server. It is recommended to use the program on a local web server or a remote server with restricted access (e.g. via a .htaccess file).

## Usage

Create a folder called `extensions` inside the unpacked folder. Your extensions need to be placed inside this folder. You can do this either by copying your code base to the folder or, for convenience, using symlinks to the code base.

In your web browser, navigate to the index.php file. You should see a form. Enter the name of your extension (e.g. `com_mycomponent` or `mod_mymodule`) or select it from the list of proposals.

## Output

The program analyzes the language files and your code base and prints the results.

### Language Files

The program lists all language files it has analyzed, grouped by language and split between administrator and site files for components. Other types of extensions have only one part.

### Missing Strings

Here you will find all untranslated language strings - language strings found in the code base, but not inside the language files. 
Language strings are recognized if they follow the scheme of `EXTENSION_[A-Z_]*`, e.g. `COM_MYCOMPONENT_ERROR_NOT_FOUND`.

### Unused Strings

The program lists all language strings that are translated in the language file, but not found in the code base.

### Components: Site / Admin

The strings for the `admin` part are those found at these parts of the code base:

- `.php` files inside the `admin` directory
- `.xml` files inside the `admin` directory
- `.xml` files inside the `site` directory, because they contain menu parameters that are displayed in the backend

The strings for the `site` part are those found at these parts of the code base:

- `.php` files inside the `site` directory
- `.xml` files inside the following directories, because they might contain parameters used in frontend forms:
  - `admin/model/field`
  - `admin/model/form`
  - `admin/models/fields`
  - `admin/models/forms`

## Please note...

- The program only recognizes complete strings. If you have a construction like `JText::_('COM_MYCOMPONENT_TITLE_' . ($isNew ? 'NEW' : 'EDIT'))` and the two strings `COM_MYCOMPONENT_TITLE_NEW` and `COM_MYCOMPONENT_TITLE_EDIT` translated in your language files, the program will recognize `COM_MYCOMPONENT_TITLE_` as untranslated and `COM_MYCOMPONENT_TITLE_NEW` and `COM_MYCOMPONENT_TITLE_EDIT` as translated, but unused. 
- Depending on which parts of your extension are displayed where (especially concerning forms and parameters), it is possible that you don't need all language strings marked as *unused* in both language files (site and admin).
- The support for `.sys.ini`files is not yet implemented.

## Contributing

If you encounter bugs, have suggestions for new features or code proposals, please open an issue or a pull request.

## License

Copyright 2018 Constantin Romankiewicz

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.


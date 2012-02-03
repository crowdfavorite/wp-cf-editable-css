## CF Editable CSS

The CF Editable CSS plugin adds the ability to insert custom styling via the WordPress admin.  This allows `Administrator` level users to create custom styling in the theme without having to FTP into the server to edit a CSS file.

The plugin has the ability to autoload the custom CSS file via `wp_enqueue_style`, or load via the Theme when desired.  If the custom CSS autoloads, it will overwrite any styling that loads before the `wp_head` function in the header of the theme.

NOTE: The `wp_head` function must be in place in the header of the theme for the Autoload function to work.

### Plugin Setup

For the plugin to work a custom.css file must be created.  There are two ways to do this

- Set the Theme folder permissions so the web server user (usually `apache`) has the ability to write to the folder
- Create a file named `custom.css` in the Theme folder, and set the file permissions so the web server user (usually `apache`) has the ability to write to it

### Creating Custom CSS

To create custom css:

- Log in to the WordPress Admin
- Click on the "CF CSS" link under the "Appearance" section of the Admin Navigation
- Select "Yes" or "No" from the Autoload dropdown
	- "Yes" will autoload the custom.css file when the `wp_head` function runs in the header of the theme
	- "No" will require the theme developer to add the custom.css file manually
- Enter any custom CSS desired in the text box in the `File Content:` section of the page.
- When finished, click the `Save CSS Changes` button to save changes

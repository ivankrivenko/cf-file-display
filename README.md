# CF File Display — Upload and Display Files in WordPress  

**Lightweight WordPress plugin** for uploading and showcasing files in posts and pages using Carbon Fields.  
Perfect for managing documents, images, and other file types with a structured display.  

[![License](https://img.shields.io/badge/license-GPLv2-green)](http://www.gnu.org/licenses/gpl-2.0.html)  
![CF File Display Preview](preview.gif) *(Example: File list displayed on a post)*  

## 🔥 Features  
- **File Uploads** — Seamlessly upload files to posts and static pages.  
- **Structured Display** — Showcases files in an organized list.  
- **Multiple File Types** — Supports various file formats (documents, images, etc.).  
- **Easy Integration** — Built on the Carbon Fields library for simple setup.  

## 🛠 Installation  
1. Ensure the **Carbon Fields** plugin is installed and activated.  
2. Upload the plugin to `/wp-content/plugins/cf-file-display` or install via the WordPress plugins screen.  
3. Activate the plugin from the WordPress 'Plugins' screen.  

**Example Setup:**  
```bash
# Upload plugin folder to:
/wp-content/plugins/cf-file-display
```

## ⚙️ Configuration  
No complex setup required! Once activated, file upload fields are available in the post/page edit screen via Carbon Fields.  

| Setting           | Description                              | Notes                          |
|-------------------|------------------------------------------|--------------------------------|
| Supported Types   | Posts, Pages                             | Custom post types not supported |
| File Types        | Images, Documents, etc.                 | Configurable via Carbon Fields |
| Display Location  | Front-end post/page content             | Automatic list rendering       |

## 📜 Examples  
**Uploading Files:**  
- Navigate to the post or page edit screen.  
- Use the Carbon Fields interface to upload files directly.  

**Displaying Files:**  
- Files are automatically rendered as a structured list on the front end.  
- Example output:  
```html
<ul class="cf-file-list">
  <li><a href="file-url">Document.pdf</a></li>
  <li><a href="file-url">Image.jpg</a></li>
</ul>
```

## 📌 FAQ  
**Q: How do I upload files?**  
A: Upload files directly from the post or page edit screen in the Carbon Fields interface.  

**Q: Can I use this with custom post types?**  
A: No, the plugin currently supports only posts and pages.  

**Q: Does it require Carbon Fields?**  
A: Yes, the Carbon Fields plugin must be installed and activated.  

## 🖼 Screenshots  
1. **File Display**  
   Shows a clean, structured list of files on the front end.  
2. **File Upload**  
   Simple file upload interface in the post/page edit screen.  

## 📝 Changelog  
**1.0**  
- Initial release of CF File Display.  

## 🔧 Upgrade Notice  
**1.0**  
- Initial release. No upgrade steps required.  

## 📜 License  
Licensed under **GPLv2 or later**.  
See [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html) for details.  

**Contributors:** Keyv  
**Tags:** file, files, upload, display, carbon fields  
**Requires PHP:** 5.6  
**Requires WordPress:** 4.7  
**Tested up to:** 6.2  
**Stable tag:** 1.0

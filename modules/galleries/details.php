<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Galleries extends Module {

	public $version = '2.2';

	public function info()
	{
		return array(
			'name' => array(
				'sl' => 'Galerija',
				'en' => 'Galleries',
				'el' => 'Γκαλερί',
				'de' => 'Galerien',
				'pl' => 'Galerie',
				'nl' => 'Gallerijen',
				'fr' => 'Galeries',
				'zh' => '畫廊',
				'it' => 'Gallerie',
				'ru' => 'Галереи',
				'ar' => 'معارض الصّور',
				'br' => 'Galerias',
				'cs' => 'Galerie',
				'es' => 'Galerías',
				'fi' => 'Galleriat',
				'lt' => 'Galerijos'
			),
			'description' => array(
				'sl' => 'Modul galerije vam omogoča da ustvarite albume vaših slik.',
				'en' => 'The galleries module is a simple module that lets users display images from Files.',
				'el' => 'Παρέχει την δυνατότητα στους χρήστες να δημιουργούν άλμπουμ εικόνων.',
				'de' => 'Mit dem Galerie Modul kannst du Bildergalerien anlegen.',
				'pl' => 'Moduł pozwalający tworzyć galerie zdjęć.',
				'nl' => 'De gallerij module die gebruikers in staat stelt afbeeldingsgallerijen te maken.',
				'fr' => 'Galerie est une puissante extension permettant de créer des galeries d\'images.',
				'zh' => '這是一個功能完整的畫廊模組，可以讓用戶建立自己的畫本或相簿。',
				'it' => 'Il modulo gallerie è un potente modulo che permette agli utenti di creare gallerie di immagini.',
				'ru' => 'Галереи - мощный модуль, который даёт пользователям возможность создавать галереи изображений.',
				'ar' => 'هذه الوحدة تمُكّنك من إنشاء معارض الصّور بسهولة.',
				'br' => 'O módulo de galerias é um poderoso módulo que permite aos usuários criar galerias de imagens.',
				'cs' => 'Silný modul pro vytváření a správu galerií obrázků.',
				'es' => 'Galerías es un potente módulo que permite a los usuarios crear galerías de imágenes.',
				'fi' => 'Galleria moduuli antaa käyttäjien luoda kuva gallerioita.',
				'lt' => 'Galerijos modulis leidžia vartotojams kurti nuotraukų galerijas'
			),
			'frontend' => TRUE,
			'backend' => TRUE,
			'menu' => 'content',

		    'shortcuts' => array(
				array(
			 	   'name' => 'galleries.new_gallery_label',
				   'uri' => 'admin/galleries/create',
				   'class' => 'add'
				),
			),
		);
	}
	
	public function install()
	{
		 $this->tables('add');
		 $this->settings('add');
		 $this->folders('add');
		
		return TRUE;
	}
	
	public function uninstall()
	{
		$this->tables('remove');
		$this->settings('remove');
		$this->folders('remove');
		
		return TRUE;
	}
	
	public function upgrade($old_version)
	{
		if($old_version <= 2.1){
			$this->db->query("ALTER TABLE  `default_galleries` CHANGE  `thumbnail_id`  `thumbnail_id` CHAR( 15 ) NULL DEFAULT NULL ;");
		}
		return TRUE;
	}
	/**
     * add or remove gallery tables.
     * 
     * @return bool
     */
	public function tables($action = 'add')
	{
		if($action == 'add')
		{
			$galleries = "CREATE TABLE IF NOT EXISTS ".$this->db->dbprefix('galleries')." (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			  `user_id` int(9) NOT NULL DEFAULT '0',
			  `datenprice_id` int(11) NOT NULL DEFAULT '0',
			  `booking_id` int(9) NOT NULL DEFAULT '0',
			  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `folder_id` int(11) NOT NULL,
			  `thumbnail_id` char(15) DEFAULT NULL,
			  `description` text COLLATE utf8_unicode_ci,
			  `updated_on` int(15) DEFAULT NULL,
			  `preview` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `enable_comments` int(1) DEFAULT NULL,
			  `published` int(1) DEFAULT NULL,
			  `css` text COLLATE utf8_unicode_ci,
			  `js` text COLLATE utf8_unicode_ci,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `slug` (`slug`),
			  UNIQUE KEY `thumbnail_id` (`thumbnail_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	
			$gallery_images = "CREATE TABLE ".$this->db->dbprefix('gallery_images')." (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `file_id` CHAR( 15 ) NULL,
			  `gallery_id` int(11) NOT NULL,
			  `order` int(11) DEFAULT '0',
			  PRIMARY KEY (`id`),
			  KEY `gallery_id` (`gallery_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			
	
			if($this->db->query($galleries) && $this->db->query($gallery_images) )
			{
				return TRUE;
			}
		}
		else{
			if($this->dbforge->drop_table('galleries') && $this->dbforge->drop_table('gallery_images'))
			{
				return TRUE;
			}
		}
		
	}
	
	/**
	 * add remove gallery settings
	 */
	public function settings($action = 'add')
	{
		if($action == 'add')
		{
			$setting_data[] = array(
	            'slug' => 'per_page',
	            'title' => 'Per Page',
	            'description' => 'You can set the number of galleries to show in a single page',
	            'default' => 15,
	            'value' => '',
	            'type' => 'text',
	            'options' => '',
	            'is_required' => 1,
	            'is_gui' => 1,
	            'module' => 'galleries'
            );
            if($this->db->insert_batch('settings', $setting_data)){
		        $return = TRUE;
	        }
		}
		else{
			
            $this->db->delete('settings', array('module' => 'galleries'));
		}

		return FALSE;
	}

	/**
     * add or remove folders.
     * 
     * @access public
     * @param $action add|remove (default: 'add')
     * @return void
     */
    public function folders($action = 'add') {

        $this->load->library('files/files');
        $this->load->model('files/file_folders_m');
        //$this->load->model('settings/settings_m');

        $folder_slug = 'photo-gallery';

        if ($action == 'add') {
            	
            $parent_id = 0;
            //users uploaded image folder
            $user_folder = $this->file_folders_m->get_by(array('slug' => $folder_slug));
			if($user_folder){
				$folder = (array)$user_folder;
			}
			else{
				$user_folder = Files::create_folder($parent_id, 'Photo Gallery');
				$folder = $user_folder['data'];
			}
			
            if($folder)
			{
				$setting_data = array(
                    'slug' => $folder['slug'],
                    'title' => $folder['name'],
                    'description' => 'Folder id for ' . $folder['name'],
                    'default' => 0,
                    'value' => $folder['id'],
                    'type' => 'text',
                    'options' => '',
                    'is_required' => 1,
                    'is_gui' => 1,
                    'module' => 'galleries'
                );
			}
			
			$folder_setting = $this->db->where('slug', $folder_slug)->get('settings')->num_rows;
			if($folder_setting == 0)
			{
				$this->db->insert('settings', $setting_data);
			}
			else{
				$this->db->where('slug', $folder_slug)->update('settings', $setting_data);
			}
            
            return TRUE;
			
        } else {
			// remove folder settings
            return (bool) $this->file_folders_m->delete_by('slug', $folder_slug);

        }
    }

	public function help()
	{
		// Return a string containing help info
		// You could include a file and return it here.
		return "<h4>Overview</h4>
		<p>The galleries module is a basic photo management tool. Features include drag & drop sorting and sub galleries.</p>
		<h4>Creating Galleries</h4>
		<p>To create a gallery go to Content->Files and create a new folder. Come back to Galleries and click \"Create a new gallery\" and select the folder of images that you just created in the File manager.
		Fill out the title, slug, and the (optional) Description. (The Description shows
		beside the gallery thumbnail at http://example.com/galleries). Choose whether you want to enable comments for this gallery or not
		and select Publish if you wish for the gallery to show in the list of galleries. Note: selecting Unpublish does not disable the gallery, it just
		removes it from the list at http://example.com/galleries. You can still create a navigation link directly to it and the
		gallery will be viewable. For example: http://example.com/galleries/gallery-title</p>
		<h4>Uploading Images</h4>
		<p>For instructions on how to upload images refer to the Files documentation.</p>
		<h4>Manage Gallery</h4>
		<p>Click on List Galleries->Manage. Here you may change the gallery's title, slug, description, etc. If you want a thumbnail to represent this
		gallery in the gallery list you may choose one from the dropdown and click Save. To change the order that the images are displayed in on the front-end
		simply grab the images and drag them into the proper order.</p>
		<h4>Editing an Image</h4>
		<p>From the Manage page click on the image that you would like to edit. A modal window will appear and you may change
		the title and the description of the image. You may also move the image to a different folder.</p>";
	}
}
/* End of file details.php */
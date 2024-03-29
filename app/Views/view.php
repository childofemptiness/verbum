<?php
namespace App\Views;
class View {

  private $localizations = array();
  private $controller = '';

  # Initialize keywords dictionary
  public function __construct($controller = ''){
    $this->build_locales();
    $this->controller = lcfirst($controller); 
  }
  
  # Replace keywords
   public function replace_localizations($html) {
    foreach ($this->localizations as $key => $value) {
      $html = str_replace("{\$" . $key . "\$}", $value, $html);
    }
    
    return $html;
  }
  
  # Keyword list
  protected function build_locales() {
    $this->localizations = array(
      "PAGE_TITLE" => WEB_TITLE,
      "SITE_ROOT" => SITE_ROOT,
      "MEDIA" => MEDIA,
      "HOME_TITLE" => "Home title",
      "ABOUT_TITLE" => "About Us title",
      "CONTACT_TITLE" => "Contact Us title"
    );
    
    return $this->localizations;
  }

  private $html_str;
  
  public $site_title = WEB_TITLE;
  public $page_title = "";
  
  # With page name locate resources, put them together and return it 
  # to the controller
  public function get_page($page_name) {        
    $this->html_str = "";
    $this->html_str .= $this->get_htmlhead();
    $this->html_str .= $this->get_htmlbody($page_name);
    $this->html_str .= $this->get_htmlclose();
    
    return $this->html_str;
  }
  
  # Build head template
  protected function get_htmlhead() {
    $html = "";
    $html .= $this->get_doctype();
    $html .= $this->get_openhtml();
    $html .= $this->get_head();
    
    return $html;
  }
  
  # Build body content and bottom scripts
  protected function get_htmlbody($page_name) {
    $html = "";
    $html .= $this->get_openbody($page_name);
    $html .= $this->get_header();
    $html .= $this->get_bodycont($page_name);
    $html .= $this->get_footer();
    $html .= $this->get_scripts();
    
    return $html;
  }
  
  # Close HTML document
  protected function get_htmlclose() {
    $html = "";
    $html .= $this->get_closebody();
    
    return $html;
  }
  
  # Define doctype, defaults to html5
  protected function get_doctype($doctype = "html5") {
    $dtd = "";
    
    if ($doctype == "html5") {
      $dtd .= "<!doctype html>";
      $dtd .= "\n";
    }
    
    return $dtd;
  }
  
  # Define language, defaults to english
  protected function get_openhtml($lang = "en-us") {
    $html = "";
    
    if ($lang = "en-us") {
      $html .= "<html lang=\"en\">";
      $html .= "\n";
    }
    
    return $html;
  }
  
  # Build head section
  protected function get_head() {
    $html = "";
    $html .= " <head>\n";
    if (file_exists(HTML . DS . $this->controller. DS . "temp" . DS . "meta.html")) {
      $html .= file_get_contents(HTML . DS . $this->controller . DS . "temp" . DS . "meta.html");
      $html .= "\n";
    }
    
    if ($this->page_title != "") {
      $title = $this->page_title . " | " . $this->site_title;
    }
    else{
      $title = $this->site_title;
    }
    
    $html .= "  <title>" . $title . "</title>";
    $html .= "\n";
    
    if (file_exists(HTML . DS . $this->controller . DS . "temp" . DS . "resources.html")) {
      $html .= file_get_contents(HTML . DS . $this->controller . DS . "temp" . DS . "resources.html");
      $html .= "\n";
    }
    
    $html .= " </head>";
    $html .= "\n";
    
    return $html;
  }
  
  # Open body section, define body id with page name
  protected function get_openbody($page_name) {
    $html = "";
    $html .= " <body id=\"" . $page_name . "\">";
    $html .= "\n";
    
    return $html;
  }
  
  # Build templated header (navigation, branding)
  protected function get_header() {
    $html = "";
    
    if (file_exists(HTML . DS . $this->controller . DS . "temp" . DS . "header.html")) {
      $html .= file_get_contents(HTML . DS . $this->controller . DS . "temp" . DS . "header.html");
      $html .= "\n";
    }
    
    return $html;
  }
  
  # Build specific content for the page
  protected function get_bodycont($page_name) {
    $html = "";
    
    if (file_exists(HTML . DS . $this->controller . DS . $page_name . ".html")) {
      $html .= file_get_contents(HTML . DS . $this->controller . DS . $page_name . ".html");
      $html .= "\n";
    }
    
    return $html;
  }
  
  # Build footer
  protected function get_footer() {
    $html = "";
    
    if (file_exists(HTML . DS . $this->controller . DS . "temp" . DS . "footer.html")) {
      $html .= file_get_contents(HTML . DS . $this->controller . DS . "temp" . DS . "footer.html");
      $html .= "\n";
    }
    
    return $html;
  }
  
  # Add bottom JS scripts
  protected function get_scripts() {
    $html = "";
    
    if (file_exists(HTML . DS . $this->controller . DS . "temp" . DS . "scripts.html")) {
      $html .= file_get_contents(HTML . DS . $this->controller . DS . "temp" . DS . "scripts.html");
      $html .= "\n";
    }
    
    return $html;
  }
  
  # Close body and html
  protected function get_closebody() {
    $html = "";
    $html .= " </body>\n";
    $html .= "</html>";
    $html .= "\n";
    
    return $html;
  }

  public function render($view_name) {    
    echo $view_name;
  }
}

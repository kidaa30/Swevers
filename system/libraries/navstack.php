<?
/* The navstack class enables easy breadcrumbs. It does not follow any hierarchy, but instead keeps a history of visited pages. */

class NavStack {
	
	private static $stacks = array();
	private static $instance;
	
	public function __construct() {
		self::prepare_stacks();
	}
	
	// Returns an instance
	public static function get_instance() {
		if (!self::$instance) self::$instance = new NavStack();
		return self::$instance;
	}
	
	// Add the current page to the stack with the specified title.
	public function add($title) {
		$new = array(
			'title' => $title,
			'url' => self::get_current_url()
		);
		if (end(self::$stacks[self::get_current_url()]) != $new) self::$stacks[self::get_current_url()][self::get_current_url()] = $new;
		self::save_stacks();
	}
	
	// Clears the stack. To be used when the current page has no parent.
	public function clear() {
		self::$stacks[self::get_current_url()] = array();
		self::save_stacks();
	}
	
	// Pops the last item of the stack
	public function remove_last() {
		array_splice(self::$stacks[self::get_current_url()],-1);
		self::save_stacks();
	}
	
	// Returns the stack as an array
	public function get_current_stack() {
		return self::$stacks[self::get_current_url()];
	}
	
	// Processes the current page and its relation to its referer
	private function prepare_stacks() {
		// Loads existing stacks if present
		if (isset($_SESSION['navstacks'])) self::$stacks = $_SESSION['navstacks'];
		
		// Only keep a record of the last 10 pages
		if (count(self::$stacks) > 10) array_splice(self::$stacks,0,count(self::$stacks)-10);
		
		// Is there a referer? Do we have a stack for that page?
		if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && isset(self::$stacks[$_SERVER['HTTP_REFERER']])) {
			
			// Get the stack that we need
			$stack = self::$stacks[$_SERVER['HTTP_REFERER']];
			
			// Push the stack to the back of the array so that it doesn't get cleared at next visit
			unset(self::$stacks[$_SERVER['HTTP_REFERER']]);
			self::$stacks[$_SERVER['HTTP_REFERER']] = $stack;
			
			// Clone the stack for use by the current page. (The referer might still be open in another tab, so we might need the original stack again later.)
			self::$stacks[self::get_current_url()] = $stack;
			
		// No referer or we don't have a stack for the referer.
		} else if (!isset(self::$stacks[self::get_current_url()])) {
			// Create a new stack
			self::$stacks[self::get_current_url()] = array();
		}
		
		// Check if the current page is already present somewhere in the stack. (The user went back to an already visited page.)
		if (array_key_exists(self::get_current_url(),self::$stacks[self::get_current_url()])) {
			// Find the position of the page in the stack, and remove any pages behind it
			$position = array_search(self::get_current_url(), array_keys(self::$stacks[self::get_current_url()]));
			if ($position !== false) array_splice(self::$stacks[self::get_current_url()],($position + 1));
		}
		
		// Save the stack to the session
		self::save_stacks();
	}
	
	// Gets the URL of the current page
	private function get_current_url() {
		return (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']?'https://':'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	
	// Saves the stacks to the session
	private function save_stacks() {
		$_SESSION['navstacks'] = self::$stacks;
	}
	
}

// Returns an instance of NavStack
function navigation_stack() {
	return NavStack::get_instance();
}
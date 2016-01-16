<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	private $connection;
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */

	public function __construct()
	{
		parent::__construct();
		$this->load->library('twitteroauth');
		$this->load->model('modelapi');
		//$this->config->load('twitter');
		$consumer_token		= 'H5mC1huULTRJ8vv32j6JFYnOY';
		$consumer_secret	= 'VvQaAcGQ7BTRDWRJpyfh6wnTNODzy0KW4kO7E4Fx8H6MUhOsEc';
		$access_token= '56232065-ExfR4xI47IvlKMGLaBmKlN68ItFrQfNxE33V2G79x'; // Optional
		$access_secret= 'Z1o7F5Y9UmukionZQ8G5OkXFQqHtGLtroo63MRUAoAmGj'; // Optional

										/*
										$sess_data['access_token'] = $access_token;
										$sess_data['consumer_token'] = $consumer_token;
										$sess_data['consumer_secret'] = $consumer_secret;
										$sess_data['access_secret'] = $access_secret;
										$this->session->set_userdata($sess_data);*/

		/*$this->connection = $this->twitteroauth->create($consumer_token, $consumer_secret, $access_token,  $access_secret);
		echo "<pre>";
		print_r($this->connection);
		exit();*/
		
		if($this->session->userdata('access_token') && $this->session->userdata('access_token_secret'))
		{
			// If user already logged in
			$this->connection = $this->twitteroauth->create($consumer_token, $consumer_secret, $access_token,  $access_secret);

			//$this->connection = $this->twitteroauth->create($consumer_token),
		}
		elseif($this->session->userdata('request_token') && $this->session->userdata('request_token_secret'))
		{
			// If user in process of authentication
			//$this->connection = $this->twitteroauth->create($this->config->item('twitter_consumer_token'), $this->config->item('twitter_consumer_secret'), $this->session->userdata('request_token'), $this->session->userdata('request_token_secret'));
			$this->connection = $this->twitteroauth->create($consumer_token, $consumer_secret, $access_token,  $access_secret);
		}	
		else
		{
			// Unknown user
			//$this->connection = $this->twitteroauth->create($this->config->item('twitter_consumer_token'), $this->config->item('twitter_consumer_secret'));
			$this->connection = $this->twitteroauth->create($consumer_token, $consumer_secret, $access_token,  $access_secret);
		}
		
	}
	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function auth()
	{
		if($this->session->userdata('access_token') && $this->session->userdata('access_token_secret'))
		{
			// User is already authenticated. Add your user notification code here.
			//redirect(base_url('/'));
			echo "Berhasil";
		}
		else
		{	
			$consumer_key		= 'H5mC1huULTRJ8vv32j6JFYnOY';
			$consumer_secret	= 'VvQaAcGQ7BTRDWRJpyfh6wnTNODzy0KW4kO7E4Fx8H6MUhOsEc';
			$token= '56232065-ExfR4xI47IvlKMGLaBmKlN68ItFrQfNxE33V2G79x'; // Optional
			$token_secret= 'Z1o7F5Y9UmukionZQ8G5OkXFQqHtGLtroo63MRUAoAmGj'; // Optional

			$host = 'api.twitter.com';
			$method = 'GET';
			$path = '/1.1/statuses/home_timeline.json'; // api call path

			$query = array( // query parameters
			    'screen_name' => 'twitterapi',
			    'count' => '5'
			);

			$oauth = array(
			    'oauth_consumer_key' => $consumer_key,
			    'oauth_token' => $token,
			    'oauth_nonce' => (string)mt_rand(), // a stronger nonce is recommended
			    'oauth_timestamp' => time(),
			    'oauth_signature_method' => 'HMAC-SHA1',
			    'oauth_version' => '1.0'
			);

			$oauth = array_map("rawurlencode", $oauth); // must be encoded before sorting
			$query = array_map("rawurlencode", $query);

			$arr = array_merge($oauth, $query); // combine the values THEN sort

			asort($arr); // secondary sort (value)
			ksort($arr); // primary sort (key)

			// http_build_query automatically encodes, but our parameters
			// are already encoded, and must be by this point, so we undo
			// the encoding step
			$querystring = urldecode(http_build_query($arr, '', '&'));

			$url = "https://$host$path";

			// mash everything together for the text to hash
			$base_string = $method."&".rawurlencode($url)."&".rawurlencode($querystring);

			// same with the key
			$key = rawurlencode($consumer_secret)."&".rawurlencode($token_secret);

			// generate the hash
			$signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_string, $key, true)));

			// this time we're using a normal GET query, and we're only encoding the query params
			// (without the oauth params)
			$url .= "?".http_build_query($query);
			$url=str_replace("&amp;","&",$url); //Patch by @Frewuill

			$oauth['oauth_signature'] = $signature; // don't want to abandon all that work!
			ksort($oauth); // probably not necessary, but twitter's demo does it

			// also not necessary, but twitter's demo does this too
			function add_quotes($str) { return '"'.$str.'"'; }
			$oauth = array_map("add_quotes", $oauth);

			// this is the full value of the Authorization line
			$auth = "OAuth " . urldecode(http_build_query($oauth, '', ', '));

			// if you're doing post, you need to skip the GET building above
			// and instead supply query parameters to CURLOPT_POSTFIELDS
			$options = array( CURLOPT_HTTPHEADER => array("Authorization: $auth"),
			                  //CURLOPT_POSTFIELDS => $postfields,
			                  CURLOPT_HEADER => false,
			                  CURLOPT_URL => $url,
			                  CURLOPT_RETURNTRANSFER => true,
			                  CURLOPT_SSL_VERIFYPEER => false);

			// do our business
			$feed = curl_init();
			curl_setopt_array($feed, $options);
			$json = curl_exec($feed);
			curl_close($feed);

			$twitter_data = json_decode($json);

			$tweetout = '';
			foreach ($twitter_data as &$value) {
			   $tweetout .= preg_replace("/(http:\/\/|(www\.))(([^\s<]{4,68})[^\s<]*)/", '<a href="http://$2$3" target="_blank">$1$2$4</a>', $value->text);
			   $tweetout = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $tweetout);
			   $tweetout = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $tweetout);
			}
			
			echo $tweetout;
			
			$posting = array(
				'tweets' => $tweetout);

			$json = $this->modelapi->savepost($posting);
			$decode = json_decode($json);
			echo "<br />";
			echo "<br />";
			echo "<br />";
			if($decode->STATUS === 'SUCCESS')
			{
				echo "<font color=blue size=12>Save Database</font>";
			}
			else
			{
				echo "Failed Save";
			}



			
		}
	}

	public function post($in_reply_to)
	{
		$message = $this->input->post('message');
		if(!$message || mb_strlen($message) > 140 || mb_strlen($message) < 1)
		{
			// Restrictions error. Notification here.
			redirect(base_url('/'));
		}
		else
		{
			if($this->session->userdata('access_token') && $this->session->userdata('access_token_secret'))
			{
				$content = $this->connection->get('account/verify_credentials');
				if(isset($content->errors))
				{
					// Most probably, authentication problems. Begin authentication process again.
					$this->reset_session();
					redirect(base_url('/twitter/auth'));
				}
				else
				{
					$data = array(
						'status' => $message,
						'in_reply_to_status_id' => $in_reply_to
					);
					$result = $this->connection->post('statuses/update', $data);

					if(!isset($result->errors))
					{
						// Everything is OK
						redirect(base_url('/'));
					}
					else
					{
						// Error, message hasn't been published
						redirect(base_url('/'));
					}
				}
			}
			else
			{
				// User is not authenticated.
				redirect(base_url('/twitter/auth'));
			}
		}
	}

	private function reset_connection()
	{
		$this->session->unset_userdata('access_token');
		$this->session->unset_userdata('access_token_secret');
		$this->session->unset_userdata('request_token');
		$this->session->unset_userdata('request_token_secret');
		$this->session->unset_userdata('twitter_user_id');
		$this->session->unset_userdata('twitter_screen_name');
	}


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
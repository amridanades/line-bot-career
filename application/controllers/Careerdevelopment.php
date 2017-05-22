<?php defined('BASEPATH') OR exit('No direct script access allowed');



// SDK for create bot

        use \LINE\LINEBot;

        use \LINE\LINEBot\HTTPClient\CurlHTTPClient;



// SDK for build message

        use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;

        use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;

        use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;

        use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;



// SDK for build button and template action

        use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;

        use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

//SDK LINEBot

use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;

use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;


class Careerdevelopment extends CI_Controller {


    private $events;

    private $signature;


    private $bot;

    private $user;


    function __construct()

    {

        parent::__construct();

        $this->load->model('M_careerdevelopment');
        // create bot object
        $httpClient = new CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
        $this->bot  = new LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

    }


    public function index()

    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            echo "Informasi Career And development";
			    
	    	header('HTTP/1.1 400 Only POST method allowed');
			
            exit;

        }


        // get request

        $body = file_get_contents('php://input');

        $this->signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE'])

                ? $_SERVER['HTTP_X_LINE_SIGNATURE']

                : "-";

        $this->events = json_decode($body, true);


        $this->M_careerdevelopment->log_events($this->signature, $body);

        foreach ($this->events['events'] as $event)

       {

       // skip group and room event

       if(! isset($event['source']['userId'])) continue;



       // get user data from database

       $this->user = $this->M_careerdevelopment->getUser($event['source']['userId']);

       // respond event

       if($event['type'] == 'message'){

           if(method_exists($this, $event['message']['type'].'Message')){

           $this->{$event['message']['type'].'Message'}($event);

           }

       }

       else {

           if(method_exists($this, $event['type'].'Callback')){

           $this->{$event['type'].'Callback'}($event);

           }

       }
    }

    }

    private function followCallback($event)

    {

        $res = $this->bot->getProfile($event['source']['userId']);

        if ($res->isSucceeded())

        {

            $profile = $res->getJSONDecodedBody();



            // save user data

            $this->M_careerdevelopment->saveUser($profile);

        }

        // send welcome message
        $message = "Hallo, " . $profile['displayName'] . "!\n";
        $message .= "Terima kasih telah menambahkan akun ini di line kamu\n";
		$message .= "Akun ini berisi informasi seputar Scholarship, Internship, dan Job Vacancies untuk mahasiswa ITB\n";
		$message .= "Ketik \"INFO\" untuk melihat informasi";
        $textMessageBuilder = new TextMessageBuilder($message);
        $this->bot->pushMessage($event['source']['userId'], $textMessageBuilder);

        $stickerMessageBuilder = new StickerMessageBuilder(1, 13);
        $this->bot->pushMessage($event['source']['userId'], $stickerMessageBuilder);
		
		
    }

    private function textMessage($event)

      {
        $userMessage = $event['message']['text'];
		$userId = $event['source']['userId'];
			
			if(strtolower($userMessage) == 'info'){
			
				$this->menu($userId);
            } 
			  
			elseif (strtolower($userMessage) == 'scholarship'){
						 
				 $this->menuScholarship($userId);			
			}
			elseif (strtolower($userMessage) == 'internship'){
						 
				 $this->menuInternship($userId);			
			}
			elseif (strtolower($userMessage) == 'job'){
						 
				 $this->menuJob($userId);			
			}
			elseif (strtolower($userMessage) == 'beasiswa bulan ini'){
			
				 $message = "Berikut daftar beasiswa dengan tanggal deadline maksimal 30 hari kedepan\n";
			   	 $textMessageBuilder = new TextMessageBuilder($message);
				 $this->bot->pushMessage($event['source']['userId'], $textMessageBuilder);	
				 
				 $this->tampilPerBulan($userId,"scholarship","nama_scholarship");			
			}
			elseif (strtolower($userMessage) == 'beasiswa dalam negeri'){
			
				 $message = "Berikut daftar beasiswa dengan tanggal deadline maksimal 30 hari kedepan\n";
				 $message .= "Dengan kategori dalam negeri";
			   	 $textMessageBuilder = new TextMessageBuilder($message);
				 $this->bot->pushMessage($event['source']['userId'], $textMessageBuilder); 
				 
				 $this->tampilPerBulanPerDesk($userId,"scholarship","nama_scholarship","Dalam Negeri");	
					
			}
			elseif (strtolower($userMessage) == 'beasiswa luar negeri'){
			
				 $message = "Berikut daftar beasiswa dengan tanggal deadline maksimal 30 hari kedepan\n";
				 $message .= "Dengan kategori luar negeri";
			   	 $textMessageBuilder = new TextMessageBuilder($message);
				 $this->bot->pushMessage($event['source']['userId'], $textMessageBuilder); 	 
				 
				 $this->tampilPerBulanPerDesk($userId,"scholarship","nama_scholarship","Luar Negeri");	
					
			}
			elseif (strtolower($userMessage) == 'magang bulan ini'){
				
				 $message = "Berikut daftar perusahaan yang menerima magang dengan tanggal deadline maksimal 30 hari kedepan\n";
			   	 $textMessageBuilder = new TextMessageBuilder($message);
				 $this->bot->pushMessage($event['source']['userId'], $textMessageBuilder);	
				
				$this->tampilPerBulan($userId,"internship","nama_internship");			
			}
			elseif (strtolower($userMessage) == 'lihat beasiswa'){
				 $linkMagang = "https://karir.itb.ac.id/";
				 $this->lihatSemua($userId,$linkMagang);			
			}
			elseif (strtolower($userMessage) == 'lihat magang'){
				 $linkMagang = "https://karir.itb.ac.id/";
				 $this->lihatSemua($userId,$linkMagang);			
			}
			elseif (strtolower($userMessage) == 'job bulan ini'){
			
				
				 $message = "Berikut daftar lowongan kerja dengan tanggal deadline maksimal 30 hari kedepan\n";
			   	 $textMessageBuilder = new TextMessageBuilder($message);
				 $this->bot->pushMessage($event['source']['userId'], $textMessageBuilder);	
				 
				 $this->tampilPerBulan($userId,"job","nama_job");			
			}
			elseif (strtolower($userMessage) == 'lihat job'){
						 
				 $linkJob = "https://karir.itb.ac.id/";
				 $this->lihatSemua($userId,$linkJob);			
			}
			
			else {
			$message = "Kamu bisa kembali dengan cara\n";
			$message .= "Ketik \"INFO\" untuk melihat informasi";
			$textMessageBuilder = new TextMessageBuilder($message);
			
			$this->bot->pushMessage($event['source']['userId'], $textMessageBuilder);
			
			}	
						  
	  }
		
	  
	  public function beasiswaPerBulan($userId){
			$date1 = date("Y-m-d");// current date
			$now = strtotime(date("Y-m-d"));			
			$addMonth = 1;
			//Add variabel addMonth to today
			$date2 = date('Y-m-j', strtotime('+'.$addMonth.' month', $now));
			$table = "scholarship";
			$data = $this->M_careerdevelopment->ambilDataPerWaktu($table,$date1,$date2);
	
	     	if (!empty($data)){
		       foreach ($data as $datas)
		     	{			
				$message = "".$datas['nama_scholarship']."\n";
				$message .= "".$datas['deskripsi']."\n";
				$textMessageBuilder = new TextMessageBuilder($message);
				$this->bot->pushMessage($userId, $textMessageBuilder);				
	         	}
	        }else{
		        $message = "Maaf untuk periode ini kami belum bisa menampilkan data\n";
			    $message .= "ketik \"INFO\" untuk ke menu awal\n";
				$textMessageBuilder = new TextMessageBuilder($message);
				$this->bot->pushMessage($userId, $textMessageBuilder);
	    	}
	  }
	  
	  public function tampilPerBulanPerDesk($userId,$namaTable,$namaJudul,$desk){
			$date1 = date("Y-m-d");// current date
			$now = strtotime(date("Y-m-d"));			
			$addMonth = 1;
			//Add variabel addMonth to today
			$date2 = date('Y-m-j', strtotime('+'.$addMonth.' month', $now));
			$table = $namaTable;
			$data = $this->M_careerdevelopment->ambilDataPerWaktuPerDesk($table,$date1,$date2,$desk);
			
			
	     	if (!empty($data)){
				$no = 1;
		       foreach ($data as $datas)
		     	{
				
				$message = "".$no.". ".$datas[$namaJudul]."\n";
				$message .= "".$datas['deskripsi']."\n";
				$message .= "Untuk info lebih lengkap klik link dibawah ini\n".$datas['link_gambar']."";
				$textMessageBuilder = new TextMessageBuilder($message);
				$this->bot->pushMessage($userId, $textMessageBuilder);			
				
				//$image = new ImageMessageBuilder($datas['link_gambar'],$datas['link_gambar']);
				//$this->bot->pushMessage($userId, $image);	

			//	$image = new UriTemplateActionBuilder('Info Lengkap', $datas['link_gambar']);
			//	$this->bot->pushMessage($userId, $image);
	         	$no++;
				}
	        }else{
		        $message = "Maaf untuk periode ini kami belum bisa menampilkan data\n";
			    $message .= "ketik \"INFO\" untuk ke menu awal\n";
				$textMessageBuilder = new TextMessageBuilder($message);
				$this->bot->pushMessage($userId, $textMessageBuilder);
	    	}
	  }
	  
	  public function tampilPerBulan($userId,$namaTable,$namaJudul){
			$date1 = date("Y-m-d");// current date
			$now = strtotime(date("Y-m-d"));			
			$addMonth = 1;
			//Add variabel addMonth to today
			$date2 = date('Y-m-j', strtotime('+'.$addMonth.' month', $now));
			$table = $namaTable;
			$data = $this->M_careerdevelopment->ambilDataPerWaktu($table,$date1,$date2);
			
			
	     	if (!empty($data)){
				$no = 1;
		       foreach ($data as $datas)
		     	{
				
				$message = "".$no.". ".$datas[$namaJudul]."\n";
				$message .= "".$datas['deskripsi']."\n";
				$message .= "Untuk info lebih lengkap klik link dibawah ini\n".$datas['link_gambar']."";
				$textMessageBuilder = new TextMessageBuilder($message);
				$this->bot->pushMessage($userId, $textMessageBuilder);			
				
				//$image = new ImageMessageBuilder($datas['link_gambar'],$datas['link_gambar']);
				//$this->bot->pushMessage($userId, $image);	

			//	$image = new UriTemplateActionBuilder('Info Lengkap', $datas['link_gambar']);
			//	$this->bot->pushMessage($userId, $image);
	         	$no++;
				}
	        }else{
		        $message = "Maaf untuk periode ini kami belum bisa menampilkan data\n";
			    $message .= "ketik \"INFO\" untuk ke menu awal\n";
				$textMessageBuilder = new TextMessageBuilder($message);
				$this->bot->pushMessage($userId, $textMessageBuilder);
	    	}
	  }
	  
	   public function tampilPerMinggu($userId,$namaTable,$namaJudul){
			$date1 = date("Y-m-d");// current date
			$now = strtotime(date("Y-m-d"));			
			$day = 7;
			$date2 = date('Y-m-j', strtotime('+'.$day.' day', $now));
			$table = $namaTable;
			$data = $this->M_careerdevelopment->ambilDataPerWaktu($table,$date1,$date2);
			
			
	     	if (!empty($data)){
				$no = 1;
		       foreach ($data as $datas)
		     	{
				
				$message = "".$no.". ".$datas[$namaJudul]."\n";
				$message .= "".$datas['deskripsi']."\n";
				$message .= "Untuk info lebih lengkap klik link dibawah ini\n".$datas['link_gambar']."";
				$textMessageBuilder = new TextMessageBuilder($message);
				$this->bot->pushMessage($userId, $textMessageBuilder);			
				
				//$image = new ImageMessageBuilder($datas['link_gambar'],$datas['link_gambar']);
				//$this->bot->pushMessage($userId, $image);	

			//	$image = new UriTemplateActionBuilder('Info Lengkap', $datas['link_gambar']);
			//	$this->bot->pushMessage($userId, $image);
	         	$no++;
				}
	        }else{
		        $message = "Maaf untuk periode ini kami belum bisa menampilkan data\n";
			    $message .= "ketik \"INFO\" untuk ke menu awal\n";
				$textMessageBuilder = new TextMessageBuilder($message);
				$this->bot->pushMessage($userId, $textMessageBuilder);
	    	}
	  }
	  
	  
	  public function menu($userId){
		//	$imageUrl = "https://res.cloudinary.com/dhacnihww/image/upload/v1495004230/sample.jpg";
			$imgInternship = "https://res.cloudinary.com/dhacnihww/image/upload/v1495188623/internship_acbqab.jpg";
			$imgJob = "https://res.cloudinary.com/dhacnihww/image/upload/v1495188628/job_ia6nhj.jpg";
			$imgScholarship = "https://res.cloudinary.com/dhacnihww/image/upload/v1495188634/scholarship_wh2130.jpg";
                $carouselTemplateBuilder = new CarouselTemplateBuilder([
                    new CarouselColumnTemplateBuilder('SCHOLARSHIP', 'Info Beasiswa', $imgScholarship, [
                     //   new UriTemplateActionBuilder('Go to line.me', 'https://line.me'),
                          new MessageTemplateActionBuilder('Pilih', 'Scholarship'),
                    ]),
                    new CarouselColumnTemplateBuilder('INTERNSHIP', 'Info Magang', $imgInternship, [
                    //    new UriTemplateActionBuilder('Go to line.me', 'https://line.me'),
                        new MessageTemplateActionBuilder('Pilih', 'Internship'),
                    ]),
					new CarouselColumnTemplateBuilder('JOB VACANCIES', 'Info Loker', $imgJob, [
                    //    new UriTemplateActionBuilder('Go to line.me', 'https://line.me'),
                        new MessageTemplateActionBuilder('Pilih', 'Job'),
                    ]),
                ]);
				$message = "Gunakan mobile app untuk melihat pilihan, atau anda bisa ketik manual,\n";
				$message .= "pilih :\n";
				$message .= "1. ketik \"Scholarship\" untuk melihat informasi tentang beasiswa\n";
				$message .= "2. ketik \"Internship\" untuk melihat informasi tentang magang\n";
				$message .= "3. ketik \"Job\" untuk melihat informasi lowongan kerja\n";
				
				
                $templateMessage = new TemplateMessageBuilder($message, $carouselTemplateBuilder);
                $this->bot->pushMessage($userId, $templateMessage);			
		
	  }
	  
	  public function menuScholarship($userId){
		//	$url = "https://res.cloudinary.com/dhacnihww/image/upload/v1495004230/sample.jpg";
			$imgScholarship = "https://res.cloudinary.com/dhacnihww/image/upload/v1495188634/scholarship_wh2130.jpg";
			$options[] = new MessageTemplateActionBuilder("Bulan ini","Beasiswa bulan ini");
			$options[] = new MessageTemplateActionBuilder("Dalam negeri","Beasiswa dalam negeri");
			$options[] = new MessageTemplateActionBuilder("Luar Negeri","Beasiswa luar negeri");
			$options[] = new MessageTemplateActionBuilder("Lihat semua","Lihat beasiswa");
		
		// prepare button template
		
            $buttonTemplate = new ButtonTemplateBuilder("Info Beasiswa", "Pilih salah satu", $imgScholarship, $options);


            // build message
			$message = "Gunakan mobile app untuk melihat pilihan, atau anda bisa ketik manual,\n";
			$message .= "pilih :\n";
			$message .= "1. ketik \"Beasiswa bulan ini\" untuk melihat beasiswa periode bulan ini\n";
			$message .= "2. ketik \"Beasiswa dalam negeri\" untuk melihat beasiswa dalam negeri\n";
			$message .= "3. ketik \"Beasiswa luar negeri\" untuk melihat beasiswa luar negeri\n";
			$message .= "4. ketik \"Lihat beasiswa\" untuk melihat semua beasiswa\n";
			
            $messageBuilder = new TemplateMessageBuilder($message, $buttonTemplate);
            $this->bot->pushMessage($userId, $messageBuilder);
	  }
	
	  	  
	  public function menuInternship($userId){
			//$url = "https://res.cloudinary.com/dhacnihww/image/upload/v1495004230/sample.jpg";
			$imgInternship = "https://res.cloudinary.com/dhacnihww/image/upload/v1495188623/internship_acbqab.jpg";
			$options[] = new MessageTemplateActionBuilder("Bulan ini","Magang bulan ini");
			$options[] = new MessageTemplateActionBuilder("Lihat semua","Lihat magang");
			//$option[] = UriTemplateActionBuilder('Lihat Semua', 'https://line.me')
		
		// prepare button template
		
            $buttonTemplate = new ButtonTemplateBuilder("Info Magang", "Pilih salah satu", $imgInternship, $options);


            // build message
			$message = "Gunakan mobile app untuk melihat pilihan, atau anda bisa ketik manual,\n";
			$message .= "pilih :\n";
			$message .= "1. ketik \"Magang bulan ini\" untuk melihat info magang periode minggu ini\n";
			$message .= "2. ketik \"Lihat magang\" untuk melihat semua info magang\n";
			
            $messageBuilder = new TemplateMessageBuilder($message, $buttonTemplate);
            $this->bot->pushMessage($userId, $messageBuilder);
	  }
	  
	  public function menuJob($userId){
			$url = "https://res.cloudinary.com/dhacnihww/image/upload/v1495004230/sample.jpg";
			$imgJob = "https://res.cloudinary.com/dhacnihww/image/upload/v1495188628/job_ia6nhj.jpg";
			$options[] = new MessageTemplateActionBuilder("Bulan Ini","Job bulan ini");
			$options[] = new MessageTemplateActionBuilder("Lihat Semua","Lihat job");
			
		
		// prepare button template
		
            $buttonTemplate = new ButtonTemplateBuilder("Lowongan Pekerjaan ", "Pilih salah satu", $imgJob, $options);


            // build message
			$message = "Gunakan mobile app untuk melihat pilihan, atau anda bisa ketik manual,\n";
			$message .= "pilih :\n";
			$message .= "1. ketik \"Job bulan ini\" untuk melihat lowongan pekerjaan periode minggu ini\n";
			$message .= "2. ketik \"Lihat job\" untuk melihat semua lowongan pekerjaan \n";
			
			
            $messageBuilder = new TemplateMessageBuilder($message, $buttonTemplate);
            $this->bot->pushMessage($userId, $messageBuilder);
	  }
	  
	  public function lihatSemua($userId,$link){
	  
			$message = "Untuk melihat semua data anda bisa klik link dibawah ini\n";
			$message .= "".$link."";
			$textMessageBuilder = new TextMessageBuilder($message);
			$this->bot->pushMessage($userId, $textMessageBuilder);
					
	  }
	  
     

}
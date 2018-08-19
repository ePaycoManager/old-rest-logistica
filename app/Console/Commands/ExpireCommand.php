<?php
	namespace App\Console\Commands;
	use App\User;
	use Illuminate\Console\Command;
	use Symfony\Component\Console\Input\InputOption;
	class ExpireCommand extends Command {
		/**
		 * The console command name.
		 *
		 * @var string
		 */
		protected $signature = "expire:token";
		
		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = "Verifica y elimina los token generados por acceso mayores a 40-45 minutos.";
		/**
		 * Execute the console command.
		 *
		 * @return void
		 */
		
		public function handle()
		{
			$time = new \DateTime('now');
			$time = $time->modify('-40 minutes');
			\DB::table('users')
			   ->where('updated_at','<',$time)
			   ->update(['api_token'=>'']);
		}
	}
<?php
	/** Nama class Database, berisi fungsi berkaitan database
	 *  Fungsi : Conn() -> Koneksi database
	 *			 get() -> Select All
	 */
	class Database{
		/** Nama fungsi conn(), untuk koneksi database
		 *  return Object mysqli_connect()
		 */ 
		function conn(){
			return mysqli_connect('localhost','root','','dataset');
		}

		/** Nama fungsi get($limit), untuk select all dengan limit dari database
		 *  parameter $limit -> numberik
		 *  return Array
		 */ 
		function get($limit){
			$r = mysqli_query($this->conn(), "SELECT * FROM breastcancerwisconsin LIMIT $limit");
			$i = 0;
			foreach ($r as $k) {
				$arr[$i] = $k;
				$i++;
			}
			return $arr;
		}
	} /*END OF CLASS Database*/

	/** Nama class Fuzzy, berisi fungsi-fungsi algoritma Fuzzy
	 *  Fungsi : u_naik($KONS, $x) -> Menghitung nilai keanggotaan naik/tinggi/besar
	 *			 u_turun($KONS, $x) -> Menghitung nilai keanggotaan turun/rendah/kecil
	 *			 u_segitiga($KONS, $x) -> Menghitung nilai keanggotaan tengah/sedang
	 *			 fuzzyfikasi($KONS, $x) -> Menghitung Nilai keanggotaan
	 */
	class Fuzzy{
		/** Nama fungsi u_naik, untuk menghitung keanggotaan naik
		 *	Bentuk:	 ___
		 *	        /
		 *  Parameter $KONS -> Array numberik, $x -> numberik
		 *  return numberik
		 */
		function u_naik($KONS, $x){
			if (count($KONS) == 2) {
				if ($x < $KONS[0]) {
					return 0;
				} else if ($KONS[0] <= $x AND $x <=$KONS[1]) {
					return ($x-$KONS[0])/(($KONS[1]-$KONS[0])+1);
				} else {
					return 1;
				}	
			} else if (count($KONS) == 3) {
				if ($x < $KONS[1]) {
					return 0;
				} else if ($KONS[1] <= $x AND $x <=$KONS[2]) {
					return ($x-$KONS[1])/(($KONS[2]-$KONS[1])+1);
				} else {
					return 1;
				}
			}
		}

		/** Nama fungsi u_turun, untuk menghitung keanggotaan turun
		 *	Bentuk: ___
		 *	           \
		 *  Parameter $KONS -> Array numberik, $x -> numberik
		 *  return numberik
		 */
		function u_turun($KONS, $x){
			if (count($KONS) == 2) {
				if ($x <= $KONS[0]) {
					return 1;
				} else if ($KONS[0] <= $x AND $x <=$KONS[1]) {
					return ($KONS[1]-$x)/(($KONS[1]-$KONS[0])+1);
				} else {
					return 0;
				}	
			} else if (count($KONS) == 3) {
				if ($x <= $KONS[0]) {
					return 1;
				} else if ($KONS[0] <= $x AND $x <=$KONS[1]) {
					return ($KONS[1]-$x)/(($KONS[1]-$KONS[0])+1);
				} else {
					return 0;
				}
			}
		}

		/** Nama fungsi u_segitiga, untuk menghitung keanggotaan segitiga
		 *	Bentuk:	/\
		 *	       /  \
		 *  Parameter $KONS -> Array numberik, $x -> numberik
		 *  return numberik
		 */
		function u_segitiga($KONS, $x){
			if ($x <= $KONS[0] OR $x >= $KONS[2]) {
				return 0;
			} else if ($KONS[0] < $x AND $x <= $KONS[1]){
				return ($x - $KONS[0])/(($KONS[1]-$KONS[0])+1);
			} else {
				return ($KONS[2]-$x)/(($KONS[2]-$KONS[1])+1);
			}
		}

		/** Nama fungsi fuzzyfikasi, untuk proses fuzzyfikasi
		 *  Parameter $kons -> Array numberik, $x -> numberik
		 *  return Array numberik
		 */
		function fuzzyfikasi($kons, $x){
			if (count($kons) == 3) {
				$r['u_naik'] = $this->u_naik($kons, $x);
				$r['u_segitiga'] = $this->u_segitiga($kons, $x);
				$r['u_turun'] = $this->u_turun($kons, $x);
				return $r;
			} else if (count($kons) == 2){
				$r['u_naik'] = $this->u_naik($kons, $x);
				$r['u_turun'] = $this->u_turun($kons, $x);
				return $r;
			}
		}

		/** Nama fungsi defuzzy_naik, untuk menetukan nilai defuzzy naik
		 *  Parameter $kons -> Array numberik, $x -> Numberik
		 *  return Numberik 
		 */
		function defuzzy_naik($kons, $x){
			if ($x == 1) {
				return 10;
			} else if ($x == 0) {
				return 0;
			} else {
				return $kons[0] + ($x * ($kons[1]-$kons[0]));
			}
		}

		/** Nama fungsi defuzzy_turun, untuk menetukan nilai defuzzy turun
		 *  Parameter $kons -> Array numberik, $x -> Numberik
		 *  return Numberik 
		 */
		function defuzzy_turun($kons, $x){
			if ($x == 1) {
				return 0;
			} else if ($x == 0) {
				return 10;
			} else {
				return $kons[0] - ($x * ($kons[1]-$kons[0]));
			}
		}
	}/*END OF CLASS Fuzzy*/

	/** Nama Class Genetika, berisi fungsi-fungsi algoritma genetika
	 *  Fungsi : rand_kons -> membangkitkan array berisi 3 angka antara 0-9.9
	 */
	class Genetika{
		/** Nama fungsi rand_kons, untuk membuat array berisi 3 angka random
		 *  return Array numberik
		 */
		function rand_kons(){
			$x = array(mt_rand(0*10,9*10)/10, mt_rand(0*10,9*10)/10, mt_rand(0*10,9*10)/10);
			sort($x);
			if ($x[0] == $x[1] OR $x[0] == $x[2] OR $x[1] == $x[2]) {
				$this->rand_kons();
			}
			return $x;
		}

		/** Nama fungsi generate_kons, untuk membangkitkan konstanta sejumlah x
		 *  parameter $x -> numberik
		 *  return Array Numberik
		 */
		function generate_kons($x){
			for ($i=0; $i<$x ; $i++) { 
				$arr[$i] = $this->rand_kons();
			}
			return $arr;
		}

		/** Nama fungsi rand_param, untuk mengacak parameter 1-9
		 *  return Array Numberik
		 */
		function rand_param(){
			$arr = array('KR','KUS','KBS','AM','SETU','BN','BC','NN','M');
			shuffle($arr);
			return $arr;
		}

		/** Nama fungsi rand_kondisi, untuk mengacak kondisi parameter
		 *  return Array Numberik
		 */
		function rand_kondisi(){
			$x = array('u_naik','u_segitiga','u_turun');
			for ($i=0; $i<9 ; $i++) { 
				$arr[$i] = array_rand(array_flip($x));
			}
			return $arr;
		}

		/** Nama fungsi rand_target, untuk mengacak kondisi target
		 *  return Numberik
		 */
		function rand_target(){
			$x = array(2,4);
			return array_rand(array_flip($x));
		}

		/** Nama fungsi generate_rule, untuk membangkitkan rule GA
		 *  Return Array numberik
		 */
		function generate_rule(){
			$x = rand(1, 9); // Kondisi rule - seharusnya 9
			$param = $this->rand_param();
			$kondisi = $this->rand_kondisi();
			$target = $this->rand_target();

			for ($i=0; $i<$x ; $i++) { 
				$arr[$i] = array('kode' => $param[$i], 'kondisi' => $kondisi[$i]);
			}
			$arr[$x] = $target;
			return $arr;
		}

		/** Nama fungsi rand_defuzzy, untuk membangkitkan nilai defuzzy
		 *  Return Array Numberik
		 */
		function rand_defuzzy(){
			$arr = array(rand(1,9), rand(1,9));
			sort($arr);
			if ($arr[0] == $arr[1]) {
				$this->rand_defuzzy();
			}
			return $arr;
		}

		/** Nama fungsi generate_gen, untuk membangkitkan gen
		 *  return Array Array Numberik
		 */
		function generate_gen(){
			$x = rand(1, 25); //Banyak rule
			for ($i=0; $i<$x ; $i++) { 
				$rule[$i] = $this->generate_rule();
			}
			$kons = $this->generate_kons(9);
			$defuzzy = $this->rand_defuzzy();
			return array('kons' => $kons, 'rule' => $rule, 'defuzzy' => $defuzzy);
		}

		/** Nama fungsi cut_poin, untuk menentukan cut point
		 *  return Numberik
		 */
		function cut_point(){
			$arr = array('kons','rule','defuzzy');
			$x = rand(0,2);
			return $arr[$x];
		}

		/** Nama fungsi cross, untuk menentukan array yang tercross over
		 *  return Array Numberik
		 */
		function cross(){
			$arr = array(0,1,2,3,4,5);
			shuffle($arr);
			return array($arr[0], $arr[1], $arr[2]);
		}

		/** Nama fungsi cross_over, untuk melakukan cross over
		 *  Parameter : $gen->array numberik 
		 *  return Array Numberik -> Gen Baru setelah Cross Over
		 */
		function cross_over($gen){
			$cp = $this->cut_point(); /* Menentukan Cut Point */
			$cr = $this->cross(); /* Menentukan Gen Yang Tercross Over */

			//Cross Over Pertama
			$temp = $gen[$cr[0]][$cp];
			$gen[$cr[0]][$cp] = $gen[$cr[1]][$cp];
			$gen[$cr[1]][$cp] = $temp;

			//Cross Over Kedua
			$temp = $gen[$cr[0]][$cp];
			$gen[$cr[0]][$cp] = $gen[$cr[2]][$cp];
			$gen[$cr[2]][$cp] = $temp;

			//Cross Over Ketiga
			$temp = $gen[$cr[1]][$cp];
			$gen[$cr[1]][$cp] = $gen[$cr[2]][$cp];
			$gen[$cr[2]][$cp] = $temp;	
			return $gen;
		}

		/** Nama fungsi mutasi, untuk proses mutasi
		 *  Parameter : $gen->array numberik 
		 *  return Array Numberik -> Gen Baru setelah Mutasi
		 */
		function mutasi($gen){
			$x = rand(0, 5);
			$arr = array('kons', 'rule', 'defuzzy');
			$y = $arr[rand(0, 2)];

			if ($y == 'kons') {
				$z = rand(0, 8);
				$gen[$x][$y][$z] = $this->rand_kons();
			} else if ($y == 'rule') {
				$z = rand(0, count($gen[$x]['rule']));
				$gen[$x][$y][$z] = $this->generate_rule();
			} else if ($y == 'defuzzy') {
				$gen[$x][$y] = $this->rand_defuzzy();
			}

			return $gen;
		}
	}/*END OF CLASS Genetika*/
 /*END OF FILE Class.php*/
 ?>
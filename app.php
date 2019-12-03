<?php
	/*
		Variabel terpakai
		$gen, $z, $a, $r, $t, $d, $key, $kode, $data

		Cross Over => Satu bagian diganti random cut poin
				   => Sekarang kons, rule, defuzzy
				   => Revisi jadi kons keberapa, rule keberapa
	*/
	include 'Class.php';
	$db = new Database();
	$ga = new Genetika();
	$fuzzy = new Fuzzy();
	$data = $db->get(200);

	//Bangkitkan 6 Gen
	for ($i=0; $i<6 ; $i++) { 
		$gen[$i] = $ga->generate_gen();
	}

	for ($Generasi=0; $Generasi<200; $Generasi++) { // 10 Generasi 
		$y = 0;
		foreach ($gen as $x) {	// Proses Fuzzy
			$kode = array('KR', 'KUS', 'KBS', 'AM', 'SETU', 'BN', 'BC', 'NN', 'M');
			$key = array_keys($data[0]);
			array_splice($key, 0, 1); // Hapus ID
			array_pop($key); // Hapus Target

			$error = 0; /* Nilai Error Sebagai Fungsi Objectif */
			/* Untuk Fuzzi */
			$z = 0;
			foreach ($data as $k) {
				//Fuzzyfikasi
				for ($i=0; $i<count($kode) ; $i++) { 
					$data[$z][$kode[$i]] = $fuzzy->fuzzyfikasi($x['kons'][$i], $k[$key[$i]]);
				}

				//Rule
				for ($i=0; $i<count($x['rule']); $i++) { // Ada berapa rule 
					$a = count($x['rule'][$i])-1; //Kondisi dalam satu rule
					for ($j=0; $j<$a; $j++) { //Parameter dalam tiap rule 
						$arr[$i][$x['rule'][$i][$j]['kode'].'-'.$x['rule'][$i][$j]['kondisi']] = $data[$z][$x['rule'][$i][$j]['kode']][$x['rule'][$i][$j]['kondisi']]; 
					}
					$arr[$i]['Target'] = $x['rule'][$i][$a];
				}
				$data[$z]['Rule'] = $arr;
				unset($arr, $a);

				//Ambil min sebagai prediket dari setiap rule
				for ($i=0; $i<count($data[$z]['Rule']); $i++) { 
					$arr = $data[$z]['Rule'][$i];
					array_pop($arr); /*Mengecualikan Target*/
					if (min($arr) > 0) { /*Ambil Prediket - Nilai Minimum*/
						$r[$i] = min($arr);
					} else {
						$r[$i] = '-';
					}
					$t[$i] = $data[$z]['Rule'][$i]['Target']; /*Ambil Target*/
					if ($t[$i] == 4 AND $r[$i] != '-') {// Defuzzy Naik
						$d[$i] = $fuzzy->defuzzy_naik($x['defuzzy'], $r[$i]);
					} else if ($t[$i] == 2 AND $r[$i] != '-'){//Defuzzy Turun
						$d[$i] = $fuzzy->defuzzy_turun($x['defuzzy'], $r[$i]);
					} else {
						$d[$i] = '-';
					}
				}
				$data[$z]['Prediket'] = $r;
				$data[$z]['Target'] = $t;
				$data[$z]['Defuzzy'] = $d;

				//Maksimal dari Defuzzy
				$k = array_keys($d, max($d))[0];

				//Menentukan Output
				if ($data[$z]['Defuzzy'][$k] != '-') {
					$data[$z]['Hasil'] = $data[$z]['Target'][$k];
				} else {
					$data[$z]['Hasil'] = 0;
				}
				unset($arr, $t, $r, $d, $k);
				/* Akhir Proses Fuzzy */

				/* Menghitung Error */
				if ($data[$z]['Hasil'] == $data[$z]['Kategori']) {
					$error++;
				}
				$z++;
			}
			// print_r($data);
			$fitnes[$y] = 1 / ($error+1); /* Menentukan Nilai Fitnes */
			$y++;
		}
		// print_r($fitnes);

		/* Fungsi Probabilitas */
		$total_fitnes = array_sum($fitnes);
		for ($i=0; $i<count($fitnes); $i++) { 
			$P[$i] = $fitnes[$i]/$total_fitnes; /* Nilai Probabilitas */
			if ($P[$i] == 1) {
				exit();
			}
		}
		// print_r($P);

		/* Probabilitas pada Roda Rolate */
		for ($i=0; $i<count($P); $i++) { 
			$C[$i] = 0;
			for ($j=0; $j<=$i ; $j++) { 
				$C[$i] += $P[$j];
			}
			/* Membuat Angka Random*/
			$R[$i] = rand(1, 10000)/10000; /* Roda Roulate */
			$Cross[$i] = rand(1, 100); /* Cross Over */
		}


		/* Fungsi Roda Roulate */
		for ($i=0; $i<count($R) ; $i++) { 
			$gen_baru[$i] = $gen[0];
			for ($j=0; $j<count($R)-1; $j++) { 
				if ($R[$i] > $C[$j] AND $R[$i] < $C[$j+1]) {
					$gen_baru[$i] = $gen[$j+1];
				}	
			}
		}
		
		$gen_baru = $ga->cross_over($gen_baru); /* Cross Over*/

		for ($i=0; $i<5 ; $i++) {  /* Mutasi 5 Gen */  
			$gen_baru = $ga->mutasi($gen_baru);
		}
		echo "<strong>Generasi ke ".($Generasi+1)."</strong><br>";
		echo "P1: ".$P[0]." | P2: ".$P[1]." | P3: ".$P[2]." | P4: ".$P[3]." | P5: ".$P[4]." | P6: ".$P[5];
		echo "<br>";

		$gen = $gen_baru;
	}

	$max = max($P);
	$index = array_search($max, $P);
	echo "<br><br><strong>Gen Terbaik :</strong><br><strong>Probabilitas: </strong> ".$max;
	
	echo "<br><strong>Konstanta :</strong><br>";
	for ($i=0; $i<9; $i++) { 
		echo "Kons : ".$key[$i]." | ".$gen[$index]['kons'][$i][0]." - ".$gen[$index]['kons'][$i][1]." - ".$gen[$index]['kons'][$i][2]."<br>";
	}
	echo "<br><strong>Rule :</strong><br>";

	for ($i=0; $i<count($gen[$index]['rule']) ; $i++) { 
		echo "Rule ke ".($i+1)." : ";
		for ($j=0; $j<count($gen[$index]['rule'][$i])-1 ; $j++) { 
			echo $gen[$index]['rule'][$i][$j]['kode']." ".$gen[$index]['rule'][$i][$j]['kondisi']." | ";
		}
		if ($gen[$index]['rule'][$i][count($gen[$index]['rule'][$i])-1] == 2) {
			echo "Jinak <br>";
		} else {
			echo "Jinak <br>";
		}
	}

	echo "<br><strong>Defuzzy :</strong><br>".$gen[$index]['defuzzy'][0]." | ".$gen[$index]['defuzzy'][1];
?>
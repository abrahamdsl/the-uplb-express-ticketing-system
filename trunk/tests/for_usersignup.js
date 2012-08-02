var testme = Array(
			"Abraha",
			"",
			"Stephanie JOanne",
			"Edriara Ann",
			"Ma. Lourdes",
			"Crestitalyn-An",
			"Toni-Jan Keith",
			"Meow--Sung",
			"Meow..Sung",
			".Gagita",
			"Putang i.",
			"Putang i-",
			"-Putang i",
			"P_utang i",
			"Putang  i"
		);

var testme = Array(
			"+639183981185",
			"09183981185",
			"9183981185",
			"418048*7",
			"03241-80487",
			"024180487",
			"+6324180487",
			"+634.94180487",
			// USA, MS's hotline
			"4257051900",
			"04257051900",
			"+14257051900",
			"0014257051900"
		);
		
var testme = Array( "aaaa@aaa.com", "abraham_dsl2@yahoo.com", "sex@yahoo.com.ph",
				"a._@yahoo.com", "@_yahoo.com", "a@a.com", "adsllave@uplb.edu.ph",
				"meong_sung@dailynk.com.kr", "_acc@att.net", "kim.jong-.un@yahoo.com" ,
				"ek.ek@yahoo.com-" , "horizon_1965@yahoo.com.ph",
				"abraham.darius.llave@gmail.com"
		);
var testme = Array(
			"200837120", "2008-37120",  "1995.20083",
			 "2008-39",  "", "&230", "2008-3712000",
			 "20083-7120"
		); 
for( var x = 0, y = testme.length; x < y; x++ ){
	console.log( testme[x] + ": " + isStudentNumber_valid(testme[x]) );
}

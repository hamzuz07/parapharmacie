<?php
die();
/*
// extract script, run it on page:
// https://webservices.amazon.com/paapi5/documentation/locale-reference.html
// https://webservices.amazon.com/paapi5/documentation/locale-reference/united-arab-emirates.html

let country = 'TR';
let rows = $('#book-search-results #search-index').next().find('tbody tr');
for(let i=0, sidx=1; i<rows.length; i++){
	let row = $( rows[i] );
	let val1 = row.find('td:first').text();
	let val2 = row.find('td:last').text();
	let browseNode = 'All' === val1 ? 0 : sidx;
	//console.log( 	row, val1, val2 );
	console.log( `insert ignore into \`{wp_prefix}amz_locale_reference\` (\`country\`, \`searchIndex\`, \`department\`, \`browseNode\`, \`sortValues\`, \`itemSearchParams\`) values('${country}', '${val1}', '${val2}', '${browseNode}', 'AvgCustomerReviews#Featured#NewestArrivals#PriceHighToLow#PriceLowToHigh#Relevance', 'Actor#Artist#Author#Availability#Brand#BrowseNode#Condition#CurrencyOfPreference#DeliveryFlags#LanguagesOfPreference#MaxPrice#Merchant#MinPrice#MinReviewsRating#MinSavingPercent#Sort#Title');` );
	//console.log( "\n" );
	if ( 'All' !== val1 ) {
		sidx++;
	}
}
*/
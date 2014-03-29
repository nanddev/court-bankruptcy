delete from citation where Fine_Amount = 0;
delete from timepay where Fine_Amount = 0;

delete from citation_cart_temp where ct_date < CURDATE();
delete from timepay_cart_temp where ct_date < CURDATE();

<head><meta charset="UTF-8">  <title>Công cụ tạo website rút gọn link</title>
</head> 
<center><br><br><h1>Tạo website link rút gọn </h1> 
<table border="0" cellpadding="0" cellspacing="0" style="width:900px;">
<tbody>
<tr>
	<td><div class="container">  
  <form id="contact" action="shopeecreat.php" method="post">
    <h3>Nền tảng Shopee Affiliate</h3>
    <h4>Điền ID shopee affiliate dạng <b>sellervn-xxxxxx</b></h4>
    <fieldset>
      <input placeholder="Tên của bạn" name="ten" type="text" tabindex="1" required autofocus maxlength="100">
    </fieldset>
    <fieldset>
      <input placeholder="Email của bạn" name="email" type="email" tabindex="2" required maxlength="100">
    </fieldset>
      <fieldset>
      <textarea placeholder="ID shopee affiliate - Có dạng sellervn-xxxxxx" name="shopeeid" tabindex="3" required maxlength="100"></textarea>
    </fieldset>
    <fieldset>
      <button name="submit" type="submit" id="contact-submit" data-submit="...Sending">Tạo trang</button>
    </fieldset>
    <p class="copyright">Designed by <a href="https://www.facebook.com/luvideezofficial/" target="_blank" title="Tạo website rút gọn link">luvideez</a></p>
  </form>
</div></td>
	<td><div class="container">  
  <form id="contact" action="ecocreat.php" method="post">
    <h3>Nền tảng Ecomobi</h3>
    <h4>Lấy token API public <a href="https://ssp.ecomobi.com/pub-api-document">Tại đây</a></h4>
    <fieldset>
      <input placeholder="Tên của bạn" name="ten" type="text" tabindex="1" required autofocus maxlength="100">
    </fieldset>
    <fieldset>
      <input placeholder="Email của bạn" name="email" type="email" tabindex="2" required maxlength="100">
    </fieldset>
      <fieldset>
      <textarea placeholder="Eco token API" name="token" tabindex="3" required maxlength="30"></textarea>
    </fieldset>
    <fieldset>
      <button name="submit" type="submit" id="contact-submit" data-submit="...Sending">Tạo trang</button>
    </fieldset>
    <p class="copyright">Tạo website Bio tại <a href="https://tibio.me/" target="_blank" title="Tạo website rút gọn link">tibio.me</a></p>
  </form>
</div></td>
</tr>
</tbody></table>
<h2>Các bạn nhớ lưu link đã tạo lại để lần sau sử dụng nhé</h2>
</center>
<style>
@import url(https://fonts.googleapis.com/css?family=Roboto:400,300,600,400italic);
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  -webkit-font-smoothing: antialiased;
  -moz-font-smoothing: antialiased;
  -o-font-smoothing: antialiased;
  font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
}

body {
  font-family: "Roboto", Helvetica, Arial, sans-serif;
  font-weight: 100;
  font-size: 12px;
  line-height: 30px;
  color: #777;
  background: #4a4747;
}

.container {
  max-width: 400px;
  width: 100%;
  margin: 0 auto;
  position: relative;
}

#contact input[type="text"],
#contact input[type="email"],
#contact input[type="tel"],
#contact input[type="url"],
#contact textarea,
#contact button[type="submit"] {
  font: 400 12px/16px "Roboto", Helvetica, Arial, sans-serif;
}

#contact {
  background: #F9F9F9;
  padding: 25px;
  margin: 150px 0;
  box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);
}

#contact h3 {
  display: block;
  font-size: 30px;
  font-weight: 300;
  margin-bottom: 10px;
}

#contact h4 {
  margin: 5px 0 15px;
  display: block;
  font-size: 13px;
  font-weight: 400;
}

fieldset {
  border: medium none !important;
  margin: 0 0 10px;
  min-width: 100%;
  padding: 0;
  width: 100%;
}

#contact input[type="text"],
#contact input[type="email"],
#contact input[type="tel"],
#contact input[type="url"],
#contact textarea {
  width: 100%;
  border: 1px solid #ccc;
  background: #FFF;
  margin: 0 0 5px;
  padding: 10px;
}

#contact input[type="text"]:hover,
#contact input[type="email"]:hover,
#contact input[type="tel"]:hover,
#contact input[type="url"]:hover,
#contact textarea:hover {
  -webkit-transition: border-color 0.3s ease-in-out;
  -moz-transition: border-color 0.3s ease-in-out;
  transition: border-color 0.3s ease-in-out;
  border: 1px solid #aaa;
}

#contact textarea {
  height: 40px;
  max-width: 100%;
  resize: none;
}

#contact button[type="submit"] {
  cursor: pointer;
  width: 100%;
  border: none;
  background: #4CAF50;
  color: #FFF;
  margin: 0 0 5px;
  padding: 10px;
  font-size: 15px;
}

#contact button[type="submit"]:hover {
  background: #43A047;
  -webkit-transition: background 0.3s ease-in-out;
  -moz-transition: background 0.3s ease-in-out;
  transition: background-color 0.3s ease-in-out;
}

#contact button[type="submit"]:active {
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.5);
}

.copyright {
  text-align: center;
}

#contact input:focus,
#contact textarea:focus {
  outline: 0;
  border: 1px solid #aaa;
}

::-webkit-input-placeholder {
  color: #888;
}

:-moz-placeholder {
  color: #888;
}

::-moz-placeholder {
  color: #888;
}

:-ms-input-placeholder {
  color: #888;
}
</style>
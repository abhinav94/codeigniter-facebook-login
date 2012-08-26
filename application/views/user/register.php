
<?php echo validation_errors(); ?>

<form action="register" method="post">

  <h5>Username</h5>
  <input type="text" name="username" value="<?=set_value('username'); ?>" size="50"/>

  <h5>Password</h5>
  <input type="password" name="password" value="" size="50" />

  <h5>Repeat Password</h5>
  <input type="password" name="repeatPassword" value="" size="50" />

  <h5>Name</h5>
  <input type="text" name="name" value="<?=set_value('name'); ?>" size="50" />

  <h5>Email Address</h5>
  <input type="email" name="email" value="<?=set_value('email'); ?>" size="50" />

  <div><input type="submit" value="Submit" /></div>

</form>
<div id="login_facebook">
  <a href='/facebook_login'>
    <img src="/images/buttons/fb-connect-large.png" />
  </a>
</div>
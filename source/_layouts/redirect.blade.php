<!DOCTYPE html>
<html lang="en-US">
<meta charset="utf-8">
<title>Redirecting&hellip;</title>
<link rel="canonical" href="@yield('redirect_url', '/')">
<script>
  location = "@yield('redirect_url', '/')"
</script>
<meta http-equiv="refresh" content="0; url=@yield('redirect_url', '/')">
<meta name="robots" content="noindex">
<h1>Redirecting&hellip;</h1>
<a href="@yield('redirect_url', '/')">Click here if you are not redirected.</a>

</html>

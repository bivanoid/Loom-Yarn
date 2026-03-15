<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<style>
  @import url(./style/global.css);

  footer {
    width: 100%;

    background-color: var(--black);
    display: grid;
    place-items: center;
    padding-block: 1em;
  }

  footer :is(p, a) {
    color: var(--primary);
    font-size: 0.8em;  
  }

  footer a {
    font-size: 1em;
    text-decoration: underline;
  }
</style>

<body>
  <footer>
    <p>&copy;Copyright - 2025 || build by <a href="https://bivanoid.site/">bivanoid</a></p>
  </footer>
</body>

</html>
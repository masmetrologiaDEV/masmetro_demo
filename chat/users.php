<?php 
  session_start();
  include_once "php/config.php";
  if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
  }
?>

<?php include_once "header.php"; ?>
<body>
  <div class="wrapper">
    <section class="section">
     <button onclick="displayFunction()" class="button">Chat</button>
    

    <section  class="users" id="sampleDiv">
      <header>
        <div class="content">
          <?php 
            $sql = mysqli_query($conn, "SELECT * FROM usuarios WHERE unique_id = {$_SESSION['unique_id']}");
            if(mysqli_num_rows($sql) > 0){
              $row = mysqli_fetch_assoc($sql);
            }
          ?>
        <!-- <img src="php/images/<?php echo $row['img']; ?>" alt="">-->
          <div class="details" >
            <span><?php echo $row['nombre']. " " . $row['paterno'] ?></span>
            <p><?php echo $row['chat_server']; ?></p>
          </div>
        </div>
        <!--<a href="php/logout.php?logout_id=<?php echo $row['unique_id']; ?>" class="logout">Logout</a>-->
      </header>
      <div class="search">
        <span class="text">Select an user to start chat</span>
        <input type="text" placeholder="Enter name to search...">
        <button><i class="fas fa-search"></i></button>
      </div>
      <div class="users-list">
  
      </div>
    </section>
    </section>
  </div>

  <script src="javascript/users.js"></script>
  <script>
    function displayFunction() {
    var sampleDiv = document.getElementById('sampleDiv');
    if (sampleDiv.style.display === 'none') {
        sampleDiv.style.display = 'block';
    } else {
        sampleDiv.style.display = 'none';
    }
}
  </script>

</body>
</html>

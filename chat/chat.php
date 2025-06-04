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
    <section class="chat-area" id="sampleDiv">
      <header>
        <?php 
          $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
          $sql = mysqli_query($conn, "SELECT * FROM usuarios WHERE unique_id = {$user_id}");
          if(mysqli_num_rows($sql) > 0){
            
            $row = mysqli_fetch_assoc($sql);
          }else{
            header("location: users.php");
          }
        ?>
        <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
        <div class="details status-dot">
          <span ><?php echo $row['nombre']. " " . $row['paterno'] ?></span>
          <p ><?php echo $row['chat_server']; ?></p>
        </div>
      </header>
      <div class="chat-box">

      </div>
      <form action="#" class="typing-area">
        <input type="text" class="incoming_id" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
        <input type="text" name="message" class="input-field" placeholder="Type a message here..." autocomplete="off">
        <button><i class="fab fa-telegram-plane"></i></button>
      </form>
    </section>
  </section>
  </div>

  <script src="javascript/chat.js"></script>
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

function  readFile(){   
  var rawFile = new XMLHttpRequest();
  rawFile.open("GET", "script.txt", true);
  rawFile.onreadystatechange = function() {
    if (rawFile.readyState === 4) {
      var allText = rawFile.responseText;
      console.log(allText);
    }
  }
  rawFile.send();
}

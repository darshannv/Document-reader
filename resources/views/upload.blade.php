<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{asset('asset/css/style.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<div class="wrapper">
  <div class="container">
      <h1>Upload a file</h1>
      <div class="upload-container">
          <div class="border-container">
              <div class="icons fa-4x">
                  <i class="fas fa-file-image" data-fa-transform="shrink-3 down-2 left-6 rotate--45"></i>
                  <i class="fas fa-file-alt" data-fa-transform="shrink-2 up-4"></i>
                  <i class="fas fa-file-pdf" data-fa-transform="shrink-3 down-2 right-6 rotate-45"></i>
              </div>
              <label for="file-upload">Drag and drop files here, or browse your computer.</label>
              <input type="file" id="file-upload" name="documentFile" style="display: none;">
          </div>
      </div>

      <button style="text-align: center" id="upload-button">Upload</button>
  </div>
</div>

<h2 style="text-align: center">Search Button</h2>

<form id="search-form" class="example" action="{{ url('/search') }}" method="GET" style="margin:auto;max-width:500px">
  <input type="text" placeholder="Search.." name="search">
  <button type="submit"><i class="fa fa-search"></i></button>
</form>


<div id="uploaded-data"></div>
<div id="original-content">
  <div id="search-results"></div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
  $(document).ready(function() {
      $('#upload-button').on('click', function(event) {
          event.preventDefault();
          $('#file-upload').click();
      });

      $('#file-upload').on('change', function(event) {
          var formData = new FormData();
          formData.append('documentFile', event.target.files[0]);

          $.ajax({
              url: '{{ url('/upload') }}',
              type: 'POST',
              data: formData,
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              contentType: false,
              processData: false,
              success: function(response) {
                $('#search-results').html(response);
    var uploadedFileName = sessionStorage.getItem('uploadedFileName');
    var uploadedContext = response.context;
    $('#uploaded-data').html('<p>Uploaded File: ' + uploadedFileName + '</p><p>' + uploadedContext + '</p>');


                  
              },
              error: function() {
                  console.log('Error uploading file');
              }
          });
      });

      $('#search-form').on('submit', function(event) {
          event.preventDefault();

          var searchKeywords = $('input[name="search"]').val();
          var uploadedFileName = sessionStorage.getItem('uploadedFileName');

          $.ajax({
              url: $(this).attr('action'),
              type: 'POST',
              data: { search: searchKeywords, uploadedFileName: uploadedFileName },
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
                $('#search-results').html(response);
    var uploadedFileName = sessionStorage.getItem('uploadedFileName');
    $('#uploaded-data').html('<p>Uploaded File: ' + uploadedFileName + '</p>');

    // Retrieve the original content
    var originalContent = $('#original-content').html();

    // Highlight the searched keyword in red
    var searchKeywords = $('input[name="search"]').val();
    if (searchKeywords !== '') {
        var regex = new RegExp('(' + searchKeywords + ')', 'gi');
        $('#search-results').html(originalContent.replace(regex, '<span class="highlight">$1</span>'));
    }
              },
              error: function() {
                  console.log('Error searching file');
              }
          });
      });
  });
</script>



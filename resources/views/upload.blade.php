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

<form id="search-form" class="example" action="{{ url('/search') }}" method="POST" style="margin:auto;max-width:500px">
  <input type="text" placeholder="Search.." name="search">
  <button type="submit"><i class="fa fa-search"></i></button>
</form>

<div class="new_container">
    <div id="uploaded-data" style="color: orange;"></div>
    <div id="search-results" style="color: green;"></div>
    <div id="original-content">
    </div>
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
            // Check if the response has the "message" key to determine success
            if (response.hasOwnProperty('message') && response.message === 'File uploaded successfully') {
                // Get the uploaded file name from the response
                var uploadedFileName = event.target.files[0].name;

                // Update the web page with the uploaded file name
                $('#uploaded-data').html('<p>Uploaded File: ' + uploadedFileName + '</p>');

                // Optionally, you can also show a success message
                $('#search-results').html('<p>File uploaded successfully.</p>');
            } else {
                // Show an error message if the response is not successful
                $('#search-results').html('<p>Error uploading file.</p>');
            }
        },
        error: function() {
           
            console.log('Error uploading file');
        }
    });
});



$('#search-form').submit(function(event) {
    event.preventDefault();
    var searchTerm = $('input[name="search"]').val();

    $.ajax({
        url: '{{ url('/search') }}',
        type: 'POST',
        data: { 'search': searchTerm },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            var searchResults = response.results;
    var searchOutput = '';

    if (searchResults.length > 0) {
        searchOutput += '<h3>Search Results:</h3>';
        searchResults.forEach(function(result) {
            searchOutput += '<p><strong>Filename: </strong>' + result.filename + '</p>';
            searchOutput += '<p>' + result.content + '</p>';
        });
    } else {
        searchOutput = '<p>No results found.</p>';
    }

    
    $('#original-content').html(searchOutput);
        },
        error: function() {
            console.log('Error searching.');
        }
    });
});

});
</script>



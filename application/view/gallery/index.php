<div class="container">
    <div class="row">
        <h1>Gallery</h1>
        <div class="upload-btn-wrapper">
            <button class="btn-upload">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-plus-square" viewBox="0 0 16 16">
                    <path
                        d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z" />
                    <path
                        d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4" />
                </svg>
            </button>
            <form id="upload-form" action="<?= Config::get('URL') . 'gallery/upload' ?>" method="post"
                enctype="multipart/form-data">
                <input type="file" name="image" id="image" style="display: none;" />
                <input type="submit" style="display: none;">
            </form>
        </div>
    </div>
    <div class="gallery">
        <?php foreach ($data['images'] as $image): ?>
            <div class="image-card">
                <div class="image-container">
                    <img src="<?= Config::get('URL') . 'gallery/display/' . $image->hash ?>" alt="Image">
                    <!-- Overlay -->
                    <div class="overlay">
                        <!-- Share icon -->
                        <a href="#" class="share <?= $image->shared ? 'shared' : '' ?>" data-id=" <?= $image->id ?>"><svg
                                xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor"
                                class="bi bi-share" viewBox="0 0 16 16">
                                <path
                                    d="M13.5 1a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.5 2.5 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5m-8.5 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m11 5.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3" />
                            </svg></a>
                        <!-- Delete icon -->
                        <a href="<?= Config::get('URL') . 'gallery/delete/' . $image->id ?>" class="delete"
                            data-id="<?= $image->id ?>"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                <path
                                    d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                <path
                                    d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                            </svg></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .container {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100vh;
        font-family: 'Roboto', sans-serif;
        border: none !important;
    }

    .row {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        background-color: #f8f8f8;
        border-bottom: 1px solid #ddd;
    }

    .gallery {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .image-card {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30%;
        margin: 1.66%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 3px;
        overflow: hidden;
    }

    .image-container {
        width: 100%;
    }

    .image-container img {
        display: block;
        width: 100%;
        height: auto;
    }

    .overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.6);
        overflow: hidden;
        width: 100%;
        height: 0;
        transition: .3s ease;
        display: flex;
        align-items: center;
        justify-content: space-around;
    }

    .share,
    .delete {
        color: white !important;
        font-size: 28px;
        text-align: center;
    }

    .image-card:hover .overlay {
        height: 100%;
    }

    .btn-upload {
        background-color: #3897f0 !important;
        color: white;
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        align-self: flex-end;
    }

    .btn-upload:hover {
        background-color: #3080e8 !important;
    }

    .share {
        color: white !important;
    }

    .share.shared {
        color: aquamarine !important;
    }
</style>

<script>
    $(document).ready(function () {
        // Add an event listener to the share icons
        $('.share').click(function () {
            // Get the image ID
            var id = $(this).data('id');
            // Check if the image is currently shared
            var isShared = $(this).hasClass('shared');
            // Send an AJAX request to the appropriate method
            $.ajax({
                url: '<?= Config::get('URL') . 'gallery/' ?>' + (isShared ? 'stopShare' : 'share') + '/' + id,
                type: 'POST',
                success: function () {
                    // Toggle the 'shared' class on the share icon
                    if (!isShared) {
                        copyShareUrl(id);
                    }
                    $(this).toggleClass('shared');
                }.bind(this)
            });
        });

        $('.btn-upload').click(function () {
            showMessage("Uploading image. Please Wait...");
            $('#image').click();
        });

        $('#image').change(function () {
            var file = this.files[0];
            var formData = new FormData();
            formData.append('image', file);

            $.ajax({
                url: '<?= Config::get('URL') . 'gallery/upload' ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = parseInt(percentComplete * 100);
                            $('.progress').show();
                            $('.progress-bar').width(percentComplete + '%');
                            if (percentComplete === 100) {
                                $('.progress').hide();
                            }
                        }
                    }, false);
                    return xhr;
                },
                success: function (data) {
                    showMessage("Successfully uplaoded new image")
                    location.reload();
                },
                error: function (data) {
                    showMessage("Image upload failed")
                }
            });
        });

        // if overlay clicked redirect to the share url
        $('.image-card').click(function (e) {
            if ($(e.target).is('.overlay')) {
                const id = $(this).find('.share').data('id');
                $.get('<?= Config::get('URL') ?>gallery/getHashFromID/' + id, function (hash) {
                    window.location.href = '<?= Config::get('URL') ?>gallery/sharedImage/' + hash;
                });
            }
        });

        $('.delete').click(function (e) {
            event.preventDefault();
            const id = $(this).data('id');
            $.ajax({
                url: `<?= Config::get('URL') ?>gallery/delete/${id}`,
                method: 'POST',
                success: function (response) {
                }
            });
            location.reload();
            showMessage('Deleted image with id: ' + id);
        });

    });
    function copyShareUrl(id) {
        // Get the image id
        var imageId = id
        console.log(imageId);
        // Make an AJAX call to get the share URL
        $.get('<?= Config::get('URL') ?>gallery/share/' + imageId, function (shareLink) {
            // Copy the share link to the clipboard
            navigator.clipboard.writeText(shareLink).then(function () {
                // Success
                showMessage('Share link copied to clipboard');
            }, function () {
                // Failure
                showMessage('Failed to copy share link');
            });
        });
    };

    function showMessage(message) {
        // Create the message div
        var messageDiv = $('<div>').text(message).css({
            position: 'fixed',
            bottom: '0',
            left: '0',
            right: '0',
            padding: '20px',
            backgroundColor: 'rgba(0, 0, 0, 0.7)',
            color: 'white',
            textAlign: 'center',
            zIndex: '1000'
        });
        // Add the message div to the body
        $('body').append(messageDiv);
        // Remove the message div after 5 seconds
        setTimeout(function () {
            messageDiv.remove();
        }, 5000);
    }
</script>
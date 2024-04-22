<div class="container">
    <div class="image-view">
        <div class="image-header">
            <div class="user-info">
                <img src="<?= $data['owner']->user_avatar_link ?>" alt="User avatar" class="avatar">
                <span class="username"><?= $data['owner']->user_name ?></span>
            </div>
            <div class="actions">
                <a href="#" class="download-button" data-id="<?= $data['image']->id ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-download" viewBox="0 0 16 16">
                        <path
                            d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5" />
                        <path
                            d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z" />
                    </svg>
                </a>
                <a href="#" class="edit-button" data-id="<?= $data['image']->id ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-pencil" viewBox="0 0 16 16">
                        <path
                            d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325" />
                    </svg>
                </a>
                <a href="#" class="share-button" data-id="<?= $data['image']->id ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-share" viewBox="0 0 16 16">
                        <path
                            d="M13.5 1a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.5 2.5 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5m-8.5 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m11 5.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3" />
                    </svg>
                </a>
                <a href="#" class="delete-button" data-id="<?= $data['image']->id ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-trash" viewBox="0 0 16 16">
                        <path
                            d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                        <path
                            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                    </svg>
                </a>
            </div>
        </div>
        <div class="image-body">
            <img src="<?= Config::get('URL') . 'gallery/display/' . $data['image']->hash ?>" alt="Image">
            <div class="image-info">
                <span class="filename"
                    contenteditable="false"><?= pathinfo($data['image']->filename, PATHINFO_FILENAME) ?>
                </span>
                <!-- invisible span with filetype -->
                <span class="filetype"
                    style="display:none;"><?= pathinfo($data['image']->filename, PATHINFO_EXTENSION) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<style>
    .image-body {
        display: flex;
        justify-content: center;
        flex-direction: column;
    }

    .container {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100vh;
        font-family: 'Roboto', sans-serif;
        border: none !important;
    }

    .image-view {
        display: flex;
        flex-direction: column;
        width: 100%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 3px;
        overflow: hidden;
    }

    .image-header,
    .image-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        background-color: #f8f8f8;
        border-bottom: 1px solid #ddd;
    }

    .image-header .user-info,
    .image-footer .caption {
        display: flex;
        align-items: center;
    }

    .image-header .user-info .avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 10px;
    }

    .image-header .actions a,
    .image-footer .actions a {
        margin-left: 15px;
        /* Add some space between the actions */
    }

    .image-container {
        width: 100%;
    }

    .image-container img {
        display: block;
        width: 100%;
        height: auto;
    }

    a {
        text-decoration: none !important;
    }
</style>

<script>
    $(document).ready(function () {
        $('.edit-button').click(function (event) {
            event.preventDefault();
            const id = $(this).data('id');
            const filenameElement = $('.filename');
            const isEditable = filenameElement.attr('contenteditable') === 'true';

            if (isEditable) {
                // If the filename is currently editable, make an AJAX call to update the filename
                const newFilename = filenameElement.text();
                const filetypeElement = $('.filetype');
                const newFiletype = filetypeElement.text();
                $.ajax({
                    url: `<?= Config::get('URL') ?>gallery/edit/${id}`,
                    method: 'POST',
                    data: { filename: newFilename, filetype: newFiletype },
                    success: function (response) {
                        // Handle the response here
                    }
                });
            } else {
                // If the filename is not currently editable, make it editable
                filenameElement.attr('contenteditable', 'true');
            }

            // Toggle the editable state
            filenameElement.attr('contenteditable', !isEditable);
        });
    });

    $('.delete-button').click(function (event) {
        event.preventDefault();
        const id = $(this).data('id');
        $.ajax({
            url: `<?= Config::get('URL') ?>gallery/delete/${id}`,
            method: 'POST',
            success: function (response) {
            }
        });
        location.reload();
    });

    $('.download-button').click(function (event) {
        event.preventDefault();
        const id = $(this).data('id');
        window.location.href = `<?= Config::get('URL') ?>gallery/download/${id}`;
    });

    $('.share-button').click(function (event) {
        event.preventDefault();
        const id = $(this).data('id');
        showMessage("Sharing settings not implemened yet")
    });

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
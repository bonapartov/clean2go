@extends('backend.layouts.master')
@section('title', __('static.chats.chats'))
@section('content')
    <div class="chatting-main-box">
        <div class="container-fluid">
            <div class="row g-md-4 g-3">
                <div class="col-12  ">
                    <div class="right-sidebar-chat">
                        <div class="contentbox">
                            <div class="inside">
                                <div class="no-data-container" id="noDataContainer">
                                    <div class="d-flex">
                                        <img src="{{ asset('admin/images/no-chat.png') }}" class="img-fluid" alt="{{ __('static.common.no_user_selected') }}">
                                    </div>
                                </div>
                                <div class="right-sidebar-title">
                                    <div class="common-space">
                                        <div class="chat-time-chat">
                                            <div class="chat-top-box">
                                                <div class="chat-profile">
                                                    <div id="receiverAvatarContainer">
                                                         @if ($admin?->media?->first())
                                                            <img class="img-fluid rounded-circle" id="receiverAvatar" src="{{ $admin?->media?->first()?->original_url }}" alt="admin">
                                                        @else
                                                            <div class="user-round message-profile">
                                                                <span>{{ strtoupper($admin?->name[0] ?? '') }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div id="receiverStatusDot"></div>
                                                </div>
                                                <div>
                                                    <h5 id="receiverName">{{ $admin?->name ?? 'Admin' }}</h5>
                                                </div>
                                            </div>
                                            <div class="chatting-option">
                                                <a href="javascript:void(0)" id="clearChat" data-bs-toggle="modal" data-bs-target="#confirmation">
                                                    <i class="ri-brush-line"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="right-sidebar-Chats">
                                    <div class="message">
                                        <div class="msger-chat custom-scrollbar" id="messages">
                                            <div id="loading">
                                                <i class="fa fa-spinner fa-spin"></i>
                                                {{ __('static.chats.load_message') }}
                                            </div>
                                            <div id="noMessages" class="no-chat-message">
                                                <span>{{ __('static.chats.no_messages_yet') }}</span>
                                            </div>
                                            <div id="error"></div>
                                        </div>
                                        <form class="msger-inputarea">
                                            <div class="position-relative">
                                                <input class="msger-input" type="text" id="message" placeholder="{{ __('static.chats.type_message') }}"><i class="ri-error-warning-line msger-input-error-icon"></i>
                                                <button class="msger-send-btn" type="button" id="send">
                                                    <i class="ri-send-plane-line"></i>
                                                </button>
                                                <input type="file" id="sendImage" accept="image/*" multiple style="display:none;">
                                                <button type="button" id="uploadImage" class="gallery" style="margin-left: 10px;">
                                                    <i class="ri-image-line "></i>
                                                </button>
                                            </div>
                                            <!-- Add Progress Bar -->
                                        </form>
                                        <div id="uploadProgress" class="progress mt-2" style="display:none;">
                                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script src="{{ asset('frontend/js/firebase/firebase-app-compat.js')}}"></script>
<script src="{{ asset('frontend/js/firebase/firebase-firestore-compat.js')}}"></script>
<script src="{{ asset('frontend/js/firebase/firebase-storage-compat.js') }}"></script>
<script>
  // Firebase configuration
  const firebaseConfig = {
    apiKey: "{{ config('firebase.firebase_api_key') }}",
    authDomain: "{{ config('firebase.firebase_auth_domain') }}",
    projectId: "{{ config('firebase.firebase_project_id') }}",
    storageBucket: "{{ config('firebase.firebase_storage_bucket') }}",
    messagingSenderId: "{{ config('firebase.firebase_messaging_sender_id') }}",
    appId: "{{ config('firebase.firebase_app_id') }}",
    measurementId: "{{ config('firebase.firebase_measurement_id') }}"
  };

  // Initialize Firebase
  firebase.initializeApp(firebaseConfig);
  
  const db = firebase.firestore();
  let unsubscribeMessages = null;

  const myUserId = "{{ auth()->user()?->id }}";
  const myUserName = "{{ auth()->user()?->name }}";
  const myUserImage = "{{ auth()->user()?->media?->first()?->original_url ?? '' }}";
  const adminId = "{{ $admin?->id }}";
  const adminName = "{{ $admin?->name ?? 'Admin' }}";
  const adminImage = "{{ $admin?->media?->first()?->original_url ?? '' }}";
  const currentChatRoomId = [myUserId, adminId].sort().join('_');
  let renderedMessageIds = new Set(); // Track rendered message IDs to prevent duplicates

  function formatTime(timestamp) {
    const date = timestamp?.toDate ? timestamp.toDate() : new Date(timestamp);
    return date.toLocaleTimeString([], {
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  // Event handler for upload button
  $('#uploadImage').on('click', function() {
    $('#sendImage').click();
  });

  $('#sendImage').on('change', function(e) {
    const files = e.target.files;
    if (files.length > 0) {
      sendImage(files); // Pass the FileList to handle multiple files
    }
  });

  function sendImage(files) {
    if (!currentChatRoomId) return;

    const receiverId = adminId;
    const receiverName = adminName;
    const totalFiles = files.length;
    let uploadedFiles = 0;
    const imageUrls = [];

    // Show progress bar
    $('#uploadProgress').show();
    $('#progressBar').css('width', '0%').attr('aria-valuenow', 0).text('0%');

    function uploadNext() {
      if (uploadedFiles >= totalFiles) {
        // All uploads complete, save to Firestore
        if (imageUrls.length > 0) {
          const messageData = {
            senderId: myUserId,
            receiverId: receiverId,
            senderName: myUserName,
            receiverName: receiverName,
            images: imageUrls,
            timestamp: firebase.firestore.FieldValue.serverTimestamp()
          };

          db.collection('support_chats').doc(currentChatRoomId).collection('messages').add(messageData)
            .then(() => {
              db.collection('support_chats').doc(currentChatRoomId).set({
                participants: [myUserId, receiverId],
                lastMessage: messageData,
                unreadCount: {
                  [receiverId]: firebase.firestore.FieldValue.increment(1),
                  [myUserId]: 0
                }
              }, { merge: true });
              $('#sendImage').val(''); // Clear input
              $('#uploadProgress').hide(); // Hide progress bar
            })
            .catch((error) => {
              console.error('Error saving message:', error);
              $('#uploadProgress').hide();
            });
        }
        return;
      }

      const file = files[uploadedFiles];
      const storageRef = firebase.storage().ref(`chatImages/${currentChatRoomId}/${Date.now()}_${file.name}`);
      const uploadTask = storageRef.put(file);

      uploadTask.on('state_changed',
        (snapshot) => {
          // Update progress for the current file
          const progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
          const overallProgress = ((uploadedFiles + (progress / 100)) / totalFiles) * 100;
          $('#progressBar').css('width', `${overallProgress}%`).attr('aria-valuenow', overallProgress);
          $('#progressBar').text(`${Math.round(overallProgress)}%`);
        },
        (error) => {
          console.error('Upload failed:', error);
          alert(`Upload failed for ${file.name}. Please try again.`);
          uploadedFiles++; // Move to next file despite failure
          uploadNext();
        },
        () => {
          // Upload completed for this file
          uploadTask.snapshot.ref.getDownloadURL().then((downloadURL) => {
            imageUrls.push(downloadURL);
            uploadedFiles++;
            uploadNext();
          });
        }
      );
    }

    // Start the upload process
    uploadNext();
  }



  function loadMessages() {
    $('#loading').show();
    $('#noMessages').hide();
    $('#messages').empty();
    renderedMessageIds.clear(); // Clear tracked message IDs on load

    // Check if the chat is cleared by the user
    db.collection('support_chats').doc(currentChatRoomId).get()
        .then((doc) => {
            const isCleared = doc.exists && doc.data().clearedBy && doc.data().clearedBy.includes(myUserId);
            const clearTimestamp = doc.exists && doc.data().clearTimestamp && doc.data().clearTimestamp[myUserId];

            // Initialize chat document
            db.collection('support_chats').doc(currentChatRoomId).set({
                participants: [myUserId, adminId],
                unreadCount: {
                    [myUserId]: 0,
                    [adminId]: 0
                },
                clearedBy: doc.exists && doc.data().clearedBy ? doc.data().clearedBy : [],
                clearTimestamp: doc.exists && doc.data().clearTimestamp ? doc.data().clearTimestamp : {}
            }, { merge: true });

            // Attach message listener with cleared state and timestamp
            attachMessageListener(isCleared, clearTimestamp);
        })
        .catch((error) => {
            $('#loading').hide();
            $('#error').text('Error checking chat status').show();
            console.error('Error checking chat status:', error);
        });
    }

  function attachMessageListener(isCleared, clearTimestamp) {
    if (unsubscribeMessages) {
        unsubscribeMessages();
        unsubscribeMessages = null;
    }

    unsubscribeMessages = db.collection('support_chats').doc(currentChatRoomId)
        .collection('messages')
        .orderBy('timestamp', 'asc')
        .onSnapshot((snapshot) => {
            $('#loading').hide();
            let hasMessages = false;

            snapshot.docChanges().forEach((change) => {
                const msg = change.doc.data();
                const messageId = change.doc.id;

                // Skip messages already rendered
                if (renderedMessageIds.has(messageId)) {
                    return;
                }

                // Filter messages based on clearTimestamp if chat is cleared
                if (isCleared && clearTimestamp && msg.timestamp) {
                    return; // Skip messages before or at clearTimestamp
                }

                if (change.type === 'added') {
                    appendMessage(messageId, msg);
                    renderedMessageIds.add(messageId); // Track rendered message
                    hasMessages = true;
                } else if (change.type === 'modified') {
                    updateMessage(messageId, msg);
                    hasMessages = true;
                } else if (change.type === 'removed') {
                    removeMessage(messageId);
                    renderedMessageIds.delete(messageId); // Remove from tracking
                }
            });

            // Update UI based on whether any messages were displayed
            if (!hasMessages && isCleared) {
                $('#noMessages').show();
            } else {
                $('#noMessages').hide();
            }

            // Update unread count
            db.collection('support_chats').doc(currentChatRoomId).set({
                unreadCount: { [myUserId]: 0 }
            }, { merge: true });

            $('#messages').scrollTop($('#messages')[0].scrollHeight);
        }, (error) => {
            $('#loading').hide();
            $('#error').text('Error loading messages').show();
            console.error('Error loading messages:', error);
        });
  }

  function appendMessage(messageId, msg) {
    const isMe = msg.senderId === myUserId;
    const bubbleClass = isMe ? 'admin-reply' : 'user-reply';
    const imageSrc = isMe ? myUserImage : adminImage;
    const imageHtml = imageSrc ?
      `<img src="${imageSrc}" class="message-profile img-fluid" alt="">` :
      `<div class="user-round message-profile"><h6>${isMe ? myUserName[0].toUpperCase() : adminName[0].toUpperCase()}</h6></div>`;

    let messageContent = '';
    if (msg.images && Array.isArray(msg.images) && msg.images.length > 0) {
      messageContent = msg.images.map(imageUrl =>
        `<img src="${imageUrl}" class="chat-image img-fluid" alt="{{ __('static.common.chat_image') }}">`
      ).join('');
    } else {
      messageContent = `<p>${msg.message || ''}</p>`;
    }

    // Use local time as fallback, will be updated by updateMessage
    const displayTimestamp = msg.timestamp || new Date();
    const html = `
      <div class="${bubbleClass}" id="msg-${messageId}">
        ${imageHtml}
        <div class="chatting-box">
          ${messageContent}
          <h6 class="timing">${formatTime(displayTimestamp)}</h6>
        </div>
      </div>
    `;
    $('#messages').append(html);
    $('#messages').scrollTop($('#messages')[0].scrollHeight);
    console.log("APPEND MSG", msg); // Debug log
  }

  function updateMessage(messageId, msg) {
    const el = $('#msg-' + messageId);
    if (el.length) {
      const isMe = msg.senderId === myUserId;
      const bubbleClass = isMe ? 'admin-reply' : 'user-reply';
      const imageSrc = isMe ? myUserImage : adminImage;
      const imageHtml = imageSrc ?
        `<img src="${imageSrc}" class="message-profile img-fluid" alt="">` :
        `<div class="user-round message-profile"><h6>${isMe ? myUserName[0].toUpperCase() : adminName[0].toUpperCase()}</h6></div>`;

      let messageContent = '';
      if (msg.images && Array.isArray(msg.images) && msg.images.length > 0) {
        messageContent = msg.images.map(imageUrl =>
          `<img src="${imageUrl}" class="chat-image img-fluid" alt="{{ __('static.common.chat_image') }}">`
        ).join('');
      } else {
        messageContent = `<p>${msg.message || ''}</p>`;
      }

      el.html(`
        <div class="${bubbleClass}" id="msg-${messageId}">
          ${imageHtml}
          <div class="chatting-box">
            ${messageContent}
            <h6 class="timing">${msg.timestamp ? formatTime(msg.timestamp) : 'Sending...'}</h6>
          </div>
        </div>
      `);
      console.log("UPDATED MSG", msg); // Debug log
    }
  }

  function removeMessage(messageId) {
    $('#msg-' + messageId).remove();
  }

  $('#send').on('click', function() {
    const $messageInput = $('#message');
    const messageText = $messageInput.val().trim();
    const receiverId = adminId;
    const receiverName = adminName;

    if (!messageText) {
      $messageInput.addClass('error');
      return;
    }
    $messageInput.removeClass('error');

    const localTimestamp = new Date(); // Local timestamp for immediate display
    const messageData = {
      senderId: myUserId,
      receiverId: receiverId,
      senderName: myUserName,
      receiverName: adminName,
      message: messageText,
      timestamp: firebase.firestore.FieldValue.serverTimestamp() // Server will resolve this
    };

    // Optimistically append with temporary ID
    const tempMessageId = Date.now().toString();
    appendMessage(tempMessageId, { ...messageData, timestamp: localTimestamp });

    // Send to Firestore
    db.collection('support_chats').doc(currentChatRoomId).collection('messages').add(messageData)
      .then((docRef) => {
        const realMessageId = docRef.id;
        // Remove temporary message and re-append with real ID
        $('#msg-' + tempMessageId).remove();
        db.collection('support_chats').doc(currentChatRoomId).set({
          participants: [myUserId, adminId],
          lastMessage: messageData,
          unreadCount: {
            [adminId]: firebase.firestore.FieldValue.increment(1),
            [myUserId]: 0
          }
        }, { merge: true });
        $messageInput.val('');
      })
      .catch((error) => {
        console.error('Error sending message:', error);
        $('#error').text('Failed to send message').show();
        $('#msg-' + tempMessageId).remove(); // Remove if failed
      });
  });

  $('#confirmDelete').on('click', function() {
    if (!currentChatRoomId) {
        $('#confirmation').modal('hide');
        return;
    }
    $(this).append('<span class="spinner-border spinner-border-sm ms-2 spinner_loader"></span>');

    // Mark chat as cleared for the user with a timestamp
    db.collection('support_chats').doc(currentChatRoomId).set({
        clearedBy: firebase.firestore.FieldValue.arrayUnion(myUserId),
        clearTimestamp: {
            [myUserId]: firebase.firestore.FieldValue.serverTimestamp()
        },
        unreadCount: {
            [myUserId]: 0,
            [adminId]: firebase.firestore.FieldValue.increment(0)
        }
    }, { merge: true })
    .then(() => {
        $('#messages').empty();
        $('#noMessages').show();
        renderedMessageIds.clear(); // Clear tracked message IDs
        if (unsubscribeMessages) {
            unsubscribeMessages();
            unsubscribeMessages = null;
        }
        $('#confirmation').modal('hide');
        $('.spinner_loader').remove();
        // Reattach listener with cleared state
        db.collection('support_chats').doc(currentChatRoomId).get()
            .then((doc) => {
                const isCleared = doc.exists && doc.data().clearedBy && doc.data().clearedBy.includes(myUserId);
                const clearTimestamp = doc.exists && doc.data().clearTimestamp && doc.data().clearTimestamp[myUserId];
                attachMessageListener(isCleared, clearTimestamp);
            });
    })
    .catch((error) => {
        console.error('Error clearing chat:', error);
        $('#error').text('Failed to clear chat').show();
        $('#confirmation').modal('hide');
        $('.spinner_loader').remove();
    });
  });

  $(document).ready(function() {
    // Initialize chat document
    db.collection('support_chats').doc(currentChatRoomId).set({
        participants: [myUserId, adminId],
        unreadCount: {
            [myUserId]: 0,
            [adminId]: 0
        }
    }, { merge: true });

    loadMessages();

    $('#message').on('keypress', function(e) {
        if (e.which === 13) {
            $('#send').click();
            return false;
        }
    });

    db.collection('users').doc(myUserId).set({
        lastActive: firebase.firestore.FieldValue.serverTimestamp()
    }, { merge: true });

    setInterval(() => {
        db.collection('users').doc(myUserId).set({
            lastActive: firebase.firestore.FieldValue.serverTimestamp()
        }, { merge: true });
    }, 30000);
  });
  </script>
@endpush

/**
 * FreePBX Call Popup
 * 
 * Listens for incoming call events and displays popup notifications
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

class CallPopup {
    constructor(options = {}) {
        this.options = {
            soundEnabled: options.soundEnabled ?? true,
            autoDismiss: options.autoDismiss ?? 0,
            onIncomingCall: options.onIncomingCall ?? null,
            ...options
        };

        this.initializeEcho();
        this.setupListeners();
    }

    initializeEcho() {
        if (window.Echo) {
            this.echo = window.Echo;
        } else {
            this.echo = new Echo({
                broadcaster: 'pusher',
                key: process.env.MIX_PUSHER_APP_KEY,
                cluster: process.env.MIX_PUSHER_APP_CLUSTER,
                forceTLS: true
            });
        }
    }

    setupListeners() {
        // Listen for incoming calls on user's extension channel
        const extension = this.options.extension;
        
        if (extension) {
            this.echo.private(`freepbx.extension.${extension}`)
                .listen('.incoming.call', (event) => {
                    this.handleIncomingCall(event);
                });
        }

        // Listen for tenant-wide calls if tenant ID is provided
        const tenantId = this.options.tenantId;
        
        if (tenantId) {
            this.echo.private(`freepbx.tenant.${tenantId}`)
                .listen('.incoming.call', (event) => {
                    this.handleIncomingCall(event);
                });
        }
    }

    handleIncomingCall(event) {
        console.log('Incoming call:', event);

        // Play notification sound
        if (this.options.soundEnabled) {
            this.playNotificationSound();
        }

        // Show popup
        this.showPopup(event);

        // Call custom handler if provided
        if (this.options.onIncomingCall) {
            this.options.onIncomingCall(event);
        }

        // Auto-dismiss if configured
        if (this.options.autoDismiss > 0) {
            setTimeout(() => {
                this.dismissPopup();
            }, this.options.autoDismiss * 1000);
        }
    }

    showPopup(event) {
        const popup = document.createElement('div');
        popup.className = 'freepbx-call-popup';
        popup.innerHTML = `
            <div class="freepbx-call-popup-content">
                <div class="freepbx-call-popup-header">
                    <h3>Incoming Call</h3>
                    <button class="freepbx-call-popup-close" onclick="this.closest('.freepbx-call-popup').remove()">×</button>
                </div>
                <div class="freepbx-call-popup-body">
                    <div class="freepbx-call-info">
                        <div class="freepbx-caller-id">
                            <strong>${event.caller_name || event.caller_id}</strong>
                        </div>
                        <div class="freepbx-phone-number">${event.caller_id}</div>
                        ${event.contact ? this.renderContactInfo(event.contact) : ''}
                    </div>
                </div>
                <div class="freepbx-call-popup-footer">
                    <small>Extension: ${event.extension}</small>
                </div>
            </div>
        `;

        document.body.appendChild(popup);

        // Animate in
        setTimeout(() => {
            popup.classList.add('show');
        }, 10);
    }

    renderContactInfo(contact) {
        return `
            <div class="freepbx-contact-info">
                <div class="freepbx-contact-name">${contact.name || 'Unknown Contact'}</div>
                ${contact.company ? `<div class="freepbx-contact-company">${contact.company}</div>` : ''}
                ${contact.email ? `<div class="freepbx-contact-email">${contact.email}</div>` : ''}
            </div>
        `;
    }

    dismissPopup() {
        const popup = document.querySelector('.freepbx-call-popup');
        if (popup) {
            popup.classList.remove('show');
            setTimeout(() => {
                popup.remove();
            }, 300);
        }
    }

    playNotificationSound() {
        const audio = new Audio('/vendor/freepbx/sounds/incoming-call.mp3');
        audio.play().catch(err => {
            console.warn('Could not play notification sound:', err);
        });
    }
}

// Export for use
window.CallPopup = CallPopup;

export default CallPopup;

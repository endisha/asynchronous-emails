<?php
/**
 * Queue list vue file
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit; 

?>
<div v-cloak>

    <h1>
        <?php esc_html_e('Queues email list', 'asynchronous-emails'); ?>
    </h1>
    <p>
        <?php esc_html_e('Stay informed with real-time updates on the status of your asynchronous emails.', 'asynchronous-emails'); ?>
    </p>

    <transition name="fade">
        <div class="error" v-if="missingNonce || errorMessage != ''">
            <p>
                {{ errorMessage }}
            </p>
        </div>
    </transition>

    <transition name="fade">
        <div class="updated" v-if="successMessage != ''">
            <p>
                {{ successMessage }}
            </p>
        </div>
    </transition>

    <transition name="fade">
        <div class="error" v-if="!active">
            <p>
                <?php esc_html_e('Warning: The asynchronous scheduler has been disabled in the settings.', 'asynchronous-emails'); ?>
            </p>
        </div>
    </transition>

    <hr />

    <div v-if="!missingNonce">

        <div class="tablenav top">

            <div class="alignleft removealignleft">

                <label for="created_at" class="inline-label">
                    <?php esc_html_e('Date', 'asynchronous-emails'); ?>
                </label>

                <input type="text" v-model="filters.created_at" id="created_at" class="form-control created_at" @keyup.enter="filterList()">

            </div>

            <div class="alignleft">

                <label for="status" class="inline-label"><?php esc_html_e('Status', 'asynchronous-emails'); ?></label>

                <select v-model="filters.status" class="form-control" id="status" @keyup.enter="filterList()">
                    <option value=""><?php esc_html_e('Any', 'asynchronous-emails'); ?></option>
                    <option v-for="(value, key) in statuses" :value="key">{{ value }}</option>
                </select>

            </div>

            <div class="alignleft">

                <button class="button button-primary" type="button" @click="filterList()" :disabled="isLoading">
                    <span v-if="!isLoading" class="dashicons dashicons-search"></span>
                    <span v-else class="spinner is-active spin spinner-button"></span>
                </button>

            </div>

        </div>

        <hr />

        <div>
            <b class="table-count"><?php esc_html_e('Count', 'asynchronous-emails'); ?>: {{ count < 0? '' : count }} 
              <i v-if="count < 0" class="spinner is-active spin spinner-button"></i></b>
        </div>
  
        <table class="wp-list-table queue widefat fixed striped table-view-list" width="100%">
            <thead>
                <tr>
                    <th class="manage-column column-id"><?php esc_html_e('#', 'asynchronous-emails'); ?></th>
                    <th class="manage-column column-email"><?php esc_html_e('Email', 'asynchronous-emails'); ?></th>
                    <th class="manage-column column-status"><?php esc_html_e('Status', 'asynchronous-emails'); ?></th>
                    <th class="manage-column column-response"><?php esc_html_e('Response', 'asynchronous-emails'); ?></th>
                    <th class="manage-column column-created_at"><?php esc_html_e('Created at', 'asynchronous-emails'); ?></th>
                    <th class="manage-column column-updated_at"><?php esc_html_e('Updated at', 'asynchronous-emails'); ?></th>
                    <th class="manage-column column-actions"><?php esc_html_e('Actions', 'asynchronous-emails'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in list" :key="item.id" v-if="!isLoading">
                    <td>{{ item.id }}</td>
                    <td>
                        {{ item.subject }}<br />
                        <span class="small">{{ item.to }}</span>
                    </td>
                    <td>
                        <span :class="statusClass(item)">
                            {{ item.localized_status }} 
                            {{ item.attempts > 0 ? `(${item.attempts})` : '' }}
                        </span>
                    </td>
                    <td>
                        <input :value="item.response" readonly="readonly" />
                    </td>
                    <td>{{ item.created_at }}</td>
                    <td>{{ item.updated_at }}</td>
                    <td>
                        <span>
                            <template>
                                <a href="#" @click.prevent="_delete(item.id)" v-if="!deleting">
                                    <?php esc_html_e('Delete', 'asynchronous-emails'); ?>
                                </a>
                                <span v-if="deleting && deletingId == item.id">
                                    <?php esc_html_e('Deleting...', 'asynchronous-emails'); ?>
                                </span>
                            </template>
                        </span>

                        <span v-if="active && item.status == 'cancelled'">
                            |
                            <template>
                                <a href="#" @click.prevent="_resned(item.id)" v-if="!sending">
                                    <?php esc_html_e('Resend', 'asynchronous-emails'); ?>
                                </a>
                                <span v-if="sending && sendingId == item.id">
                                    <?php esc_html_e('Resending...', 'asynchronous-emails'); ?>
                                </span>
                            </template>
                        </span>
                    </td>
                </tr>
                <tr v-if="list.length == 0 && !isLoading">
                    <td colspan="7"><?php esc_html_e('No records found.', 'asynchronous-emails'); ?></td>
                </tr>
                <tr v-if="isLoading">
                    <td colspan="7" class="center warning" style="text-align: center;">
                        <i class="spinner is-active spin" style="text-align: center;margin: auto;"></i>
                    </td>
                </tr>
            </tbody>
        </table>

        <paginate :count="count" :pages="pages" :current="current" @navigate="loadPage"></paginate>

    </div>
</div>
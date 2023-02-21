<?php
/**
 * Settings vue file
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit; 

?>
<div v-cloak>

    <h1>
      <?php esc_html_e('Settings', 'asynchronous-emails'); ?>
      <i v-if="isLoading" class="spinner is-active spin"></i>
    </h1>

    <transition name="fade">
        <div class="error" v-if="errorMessage != ''"><p>{{ errorMessage }}</p></div>
        <div class="updated" v-if="successMessage != ''">
            <p>{{ successMessage }}</p>
        </div>
    </transition>

    <table class="form-table" width="100%">
    <tbody>
        <tr>
          <th style="width: 200px">
            <label for="name" class="inline-label"><?php esc_html_e('Active', 'asynchronous-emails'); ?></label>
          </th>
          <td>
            <label for="active">
            <input name="active" type="checkbox" id="active" v-model="settings.active" true-value="1" false-value="0" :disabled="submitting || isLoading">
                <?php esc_html_e('Set emails as asynchronous', 'asynchronous-emails'); ?>
            </label>
          </td>
        </tr>
        <tr>
          <th>
            <label for="maximum-attempts" class="inline-label">
                <?php esc_html_e('Max attempts', 'asynchronous-emails'); ?>
            </label>
          </th>
          <td>
            <input type="number" id="maximum-attempts" class="small-text" value="3" v-model="settings.max_attempts" :disabled="submitting || isLoading">
            <p class="description" id="maximum-attempts">
                <?php esc_html_e('The maximum number of attempts for a failed task before it is set as canceled', 'asynchronous-emails'); ?>
            </p>
          </td>
        </tr>

        <tr>
          <th>
            <label for="deletion-period-every" class="inline-label">
                <?php esc_html_e('Delete records older than', 'asynchronous-emails'); ?>
            </label>
          </th>
          <td>
            <select id="max-queue-record-age" class="regular-small" v-model="settings.max_queue_record_age" :disabled="submitting || isLoading">
                <option value="1"><?php esc_html_e('one day', 'asynchronous-emails'); ?></option>
                <option value="2"><?php esc_html_e('2 days', 'asynchronous-emails'); ?></option>
                <option value="7"><?php esc_html_e('7 days', 'asynchronous-emails'); ?></option>
                <option value="15"><?php esc_html_e('15 days', 'asynchronous-emails'); ?></option>
                <option value="30"><?php esc_html_e('a month', 'asynchronous-emails'); ?></option>
            </select>
            <p class="description" id="max-queue-record-age">
                <?php esc_html_e('Automatically delete records that are older than the specified time period', 'asynchronous-emails'); ?>
            </p>
          </td>
        </tr>

        <tr>
          <th></th>
          <td> 
              <button @click="updateSettings()" type="button" class="button button-primary" id="submit" :disabled="submitting || isLoading"> 
                  <i v-if="submitting" class="spinner is-active spin spinner-button"></i> 
                  <span v-if="!submitting"><?php esc_html_e('Save Changes', 'asynchronous-emails'); ?></span>
                  <span v-else><?php esc_html_e('Saving', 'asynchronous-emails'); ?></span>
              </button>
          </td>
        </tr>

        </tbody>
    </table>

</div>
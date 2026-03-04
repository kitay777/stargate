<script setup>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import jaLocale from '@fullcalendar/core/locales/ja'
</script>

<template>
  <div style="padding: 2rem; background: white; color: black; min-height: 100vh;">
    <h1 style="font-size: 24px; margin-bottom: 1rem;">📅 カレンダー表示テスト</h1>

    <FullCalendar
      :plugins="[dayGridPlugin]"
      initialView="dayGridMonth"
      :events="[
        { title: 'テスト配信', start: '2025-05-10' }
      ]"
      :locales="[jaLocale]"
      locale="ja"
      :height="600"
    />
  </div>
</template>

<style>
.fc {
  background-color: white !important;
  color: black !important;
  font-size: 14px;
  min-height: 600px;
}
.fc table {
  table-layout: fixed !important;
  width: 100% !important;
  border-collapse: collapse !important;
  display: table !important;
}
.fc td,
.fc th,
.fc thead,
.fc tbody {
  display: table-cell !important;
  background: #fff !important;
  color: #000 !important;
  border: 1px solid #ccc !important;
  padding: 4px !important;
}
</style>
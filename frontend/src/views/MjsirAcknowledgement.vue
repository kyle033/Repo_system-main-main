<template>
  <section class="rounded-3xl border border-slate-800 bg-slate-900/60 p-8 text-slate-100">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="text-xs uppercase tracking-[0.22em] text-slate-400">MJSIR</p>
        <h1 class="text-2xl font-semibold uppercase tracking-[0.12em]">MJSIR Acknowledgement</h1>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <input
          v-model.trim="searchQuery"
          type="text"
          placeholder="Search records..."
          class="w-64 rounded-full border border-slate-700 bg-slate-950/70 px-4 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none"
        />
        <button
          type="button"
          class="rounded-full border border-emerald-500/60 bg-emerald-500/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-200 transition hover:border-emerald-400 hover:text-emerald-100"
          @click="openModal"
        >
          Add Distributed Journal
        </button>
      </div>
    </div>

    <p v-if="pageMessage" class="mt-4 text-xs text-emerald-300">{{ pageMessage }}</p>
    <p v-if="pageError" class="mt-4 text-xs text-rose-300">{{ pageError }}</p>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-800">
      <div v-if="loading" class="px-4 py-6 text-sm text-slate-300">Loading distributed journals...</div>
      <div v-else-if="error" class="px-4 py-6 text-sm text-rose-300">{{ error }}</div>
      <div v-else-if="filteredRows.length === 0" class="px-4 py-6 text-sm text-slate-300">
        No distributed journals found.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-800 text-left text-sm">
          <thead class="bg-slate-900/70 text-xs uppercase tracking-[0.16em] text-slate-400">
            <tr>
              <th class="px-4 py-3 font-semibold">Date</th>
              <th class="px-4 py-3 font-semibold">Time</th>
              <th class="px-4 py-3 font-semibold">Name</th>
              <th class="px-4 py-3 font-semibold">Position</th>
              <th class="px-4 py-3 font-semibold">Affiliation/Agency</th>
              <th class="px-4 py-3 font-semibold">Volume</th>
              <th class="px-4 py-3 font-semibold">No. of Copies</th>
              <th class="px-4 py-3 font-semibold">Remarks</th>
              <th class="px-4 py-3 font-semibold">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800 text-slate-200">
            <tr v-for="row in filteredRows" :key="row.groupKey">
              <td class="px-4 py-3">{{ formatDate(row.date_distributed) }}</td>
              <td class="px-4 py-3">{{ formatTime(row.distributed_time) }}</td>
              <td class="px-4 py-3">{{ row.recipient_name || '-' }}</td>
              <td class="px-4 py-3">{{ row.position || '-' }}</td>
              <td class="px-4 py-3">{{ row.affiliation_agency || '-' }}</td>
              <td class="px-4 py-3">{{ row.itemsLabel || '-' }}</td>
              <td class="px-4 py-3">{{ row.copiesLabel || '-' }}</td>
              <td class="px-4 py-3">{{ row.remarksLabel || '-' }}</td>
              <td class="px-4 py-3">
                <button
                  type="button"
                  class="mr-2 rounded border border-emerald-500/70 bg-emerald-500/10 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-emerald-200 hover:border-emerald-400"
                  @click="printRecord(row)"
                >
                  Print
                </button>
                <button
                  type="button"
                  class="rounded border border-sky-500/70 bg-sky-500/10 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-sky-200 hover:border-sky-400"
                  @click="openAddVolumeModal(row)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="ml-2 rounded border border-rose-500/70 bg-rose-500/10 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-rose-200 hover:border-rose-400"
                  @click="deleteRecord(row)"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <transition name="fade">
    <div
      v-if="showModal"
      class="fixed inset-0 z-40 bg-black/60"
      @click="closeModal"
    ></div>
  </transition>

  <transition name="modal">
    <div v-if="showModal" class="fixed inset-0 z-50 grid place-items-center p-4">
      <div class="w-full max-w-3xl rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-2xl" @click.stop>
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-200">Add Distributed Journal</h2>
          <button
            type="button"
            class="rounded-md border border-slate-700 px-3 py-1 text-xs uppercase tracking-[0.18em] text-slate-300 hover:border-slate-500"
            @click="closeModal"
          >
            Close
          </button>
        </div>

        <form class="grid gap-4 md:grid-cols-2" @submit.prevent="createRow">
          <div class="relative md:col-span-2 rounded-xl border border-slate-800 bg-slate-950/60 p-4">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">Distribution Schedule</p>

            <label class="text-sm">
              <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Date and Time *</span>
              <input
                readonly
                :value="displayDateTime"
                class="w-full cursor-pointer rounded-lg border border-slate-700 bg-slate-950/80 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
                @click="openDateTimePicker"
              />
            </label>

            <div v-if="pickerOpen" class="absolute left-4 right-4 top-24 z-20 rounded-xl border border-slate-700 bg-slate-900 p-4 shadow-2xl">
              <div class="grid gap-4 md:grid-cols-[1fr_220px]">
                <div>
                  <div class="mb-2 flex items-center justify-between">
                    <button type="button" class="rounded border border-slate-700 px-2 py-1 text-xs" @click="changeMonth(-1)"><</button>
                    <div class="text-sm font-semibold">{{ monthYearLabel }}</div>
                    <button type="button" class="rounded border border-slate-700 px-2 py-1 text-xs" @click="changeMonth(1)">></button>
                  </div>

                  <div class="grid grid-cols-7 gap-1 text-center text-xs text-slate-400">
                    <span v-for="w in weekDays" :key="w">{{ w }}</span>
                  </div>

                  <div class="mt-2 grid grid-cols-7 gap-1">
                    <button
                      v-for="(cell, index) in calendarCells"
                      :key="`${cell.day}-${index}`"
                      type="button"
                      class="h-8 rounded text-xs"
                      :class="[
                        cell.currentMonth ? 'text-slate-200' : 'text-slate-500',
                        isSelectedDay(cell) ? 'bg-sky-600 text-white' : 'hover:bg-slate-800'
                      ]"
                      @click="selectDay(cell)"
                    >
                      {{ cell.day }}
                    </button>
                  </div>

                  <div class="mt-3 flex items-center justify-between text-xs">
                    <button type="button" class="text-sky-300 hover:text-sky-200" @click="clearDateTime">Clear</button>
                    <button type="button" class="text-sky-300 hover:text-sky-200" @click="setTodayNow">Today</button>
                  </div>
                </div>

                <div class="border-l border-slate-700 pl-4">
                  <div class="mb-2 grid grid-cols-3 gap-2 text-center text-xs font-semibold">
                    <div class="rounded bg-sky-600 py-1">{{ pad2(pickerHour) }}</div>
                    <div class="rounded bg-sky-600 py-1">{{ pad2(pickerMinute) }}</div>
                    <div class="rounded bg-sky-600 py-1">{{ pickerMeridiem }}</div>
                  </div>

                  <div class="grid grid-cols-3 gap-2">
                    <div class="h-48 overflow-y-auto rounded border border-slate-700">
                      <button
                        v-for="h in hourOptions"
                        :key="`h${h}`"
                        type="button"
                        class="block w-full px-2 py-1 text-center text-sm hover:bg-slate-800"
                        :class="pickerHour === h ? 'bg-sky-600 text-white' : 'text-slate-200'"
                        @click="pickerHour = h"
                      >
                        {{ pad2(h) }}
                      </button>
                    </div>

                    <div class="h-48 overflow-y-auto rounded border border-slate-700">
                      <button
                        v-for="m in minuteOptions"
                        :key="`m${m}`"
                        type="button"
                        class="block w-full px-2 py-1 text-center text-sm hover:bg-slate-800"
                        :class="pickerMinute === m ? 'bg-sky-600 text-white' : 'text-slate-200'"
                        @click="pickerMinute = m"
                      >
                        {{ pad2(m) }}
                      </button>
                    </div>

                    <div class="h-48 overflow-y-auto rounded border border-slate-700">
                      <button
                        v-for="ap in meridiemOptions"
                        :key="ap"
                        type="button"
                        class="block w-full px-2 py-1 text-center text-sm hover:bg-slate-800"
                        :class="pickerMeridiem === ap ? 'bg-sky-600 text-white' : 'text-slate-200'"
                        @click="pickerMeridiem = ap"
                      >
                        {{ ap }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-3 flex justify-end gap-2">
                <button type="button" class="rounded border border-slate-700 px-3 py-1 text-xs" @click="pickerOpen = false">Cancel</button>
                <button type="button" class="rounded border border-sky-500 bg-sky-600/20 px-3 py-1 text-xs text-sky-200" @click="applyDateTime">Select</button>
              </div>
            </div>
          </div>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Name *</span>
            <input
              ref="journalTitleInput"
              v-model.trim="form.recipient_name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Position *</span>
            <input
              v-model.trim="form.position"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Affiliation/Agency *</span>
            <input
              v-model.trim="form.affiliation_agency"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Volume *</span>
            <input
              v-model.trim="form.volume"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">No. of Copies *</span>
            <input
              v-model.number="form.no_of_copies"
              type="number"
              min="1"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm md:col-span-2">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Remarks *</span>
            <textarea
              v-model.trim="form.remarks"
              rows="3"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            ></textarea>
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Issued By *</span>
            <input
              v-model.trim="form.issued_by"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Received By *</span>
            <input
              v-model.trim="form.received_by"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <div class="md:col-span-2 flex items-center gap-3">
            <p v-if="submitError" class="text-xs text-rose-300">{{ submitError }}</p>
            <p v-if="submitMessage" class="text-xs text-emerald-300">{{ submitMessage }}</p>
            <button
              type="submit"
              :disabled="submitting"
              class="rounded-full border border-emerald-500/60 bg-emerald-500/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-200 transition hover:border-emerald-400 hover:text-emerald-100 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ submitting ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>

  <transition name="fade">
    <div
      v-if="showAddVolumeModal"
      class="fixed inset-0 z-[60] bg-black/60"
      @click="closeAddVolumeModal"
    ></div>
  </transition>

  <transition name="modal">
    <div v-if="showAddVolumeModal" class="fixed inset-0 z-[70] grid place-items-center p-4">
      <div class="w-full max-w-3xl rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-2xl" @click.stop>
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-100">Edit Record</h3>
          <button
            type="button"
            class="rounded-md border border-slate-700 px-3 py-1 text-xs uppercase tracking-[0.18em] text-slate-300 hover:border-slate-500"
            @click="closeAddVolumeModal"
          >
            Close
          </button>
        </div>

        <form class="grid gap-3 md:grid-cols-2" @submit.prevent="submitEditRecord">
          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Date *</span>
            <input
              v-model="editForm.date_distributed"
              type="date"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>
          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Time *</span>
            <input
              v-model="editForm.distributed_time"
              type="time"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Name</span>
            <input
              v-model.trim="editForm.recipient_name"
              type="text"
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>
          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Position</span>
            <input
              v-model.trim="editForm.position"
              type="text"
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Affiliation/Agency</span>
            <input
              v-model.trim="editForm.affiliation_agency"
              type="text"
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <div class="md:col-span-2 rounded-xl border border-slate-800 bg-slate-950/60 p-3">
            <div class="mb-2 flex items-center justify-between">
              <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Items</p>
              <button
                type="button"
                class="rounded border border-sky-500/70 bg-sky-500/10 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-sky-200 hover:border-sky-400"
                @click="addEditItem"
              >
                Add Item
              </button>
            </div>

            <div
              v-for="(item, index) in editForm.items"
              :key="`item-${index}`"
              class="mb-2 grid items-center gap-2 md:grid-cols-[minmax(0,1fr)_96px_minmax(0,1fr)_84px]"
            >
              <input
                v-model.trim="item.volume"
                type="text"
                placeholder="Volume"
                required
                class="min-w-0 rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <input
                v-model.number="item.no_of_copies"
                type="number"
                min="1"
                required
                class="min-w-0 rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <input
                v-model.trim="item.remarks"
                type="text"
                placeholder="Remark"
                required
                class="min-w-0 rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <button
                type="button"
                class="w-full rounded border border-rose-500/60 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-rose-300 hover:border-rose-400"
                @click="removeEditItem(index)"
              >
                Remove
              </button>
            </div>
          </div>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Issued By *</span>
            <input
              v-model.trim="editForm.issued_by"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>
          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Received By *</span>
            <input
              v-model.trim="editForm.received_by"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <div class="md:col-span-2 flex items-center justify-end gap-2">
            <p v-if="submitError" class="mr-auto text-xs text-rose-300">{{ submitError }}</p>
            <p v-if="submitMessage" class="mr-auto text-xs text-emerald-300">{{ submitMessage }}</p>
            <button
              type="button"
              class="rounded-md border border-slate-700 px-3 py-2 text-xs uppercase tracking-[0.18em] text-slate-300 hover:border-slate-500"
              @click="closeAddVolumeModal"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="rounded-md border border-sky-500/70 bg-sky-500/10 px-3 py-2 text-xs uppercase tracking-[0.18em] text-sky-200 hover:border-sky-400 disabled:cursor-not-allowed disabled:opacity-60"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>

</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import axios from 'axios'

const loading = ref(false)
const error = ref('')
const rows = ref([])
const submitting = ref(false)
const submitError = ref('')
const submitMessage = ref('')
const pageError = ref('')
const pageMessage = ref('')
const searchQuery = ref('')
const showModal = ref(false)
const journalTitleInput = ref(null)
const showAddVolumeModal = ref(false)
const addVolumeSource = ref(null)
const editSourceItemIds = ref([])
const editForm = ref({
  date_distributed: '',
  distributed_time: '',
  recipient_name: '',
  position: '',
  affiliation_agency: '',
  issued_by: '',
  received_by: '',
  items: [{ volume: '', no_of_copies: 1, remarks: '' }]
})
let previousBodyOverflow = ''

const form = ref({
  date_distributed: '',
  distributed_time: '',
  recipient_name: '',
  position: '',
  affiliation_agency: '',
  issued_by: '',
  received_by: '',
  volume: '',
  no_of_copies: 1,
  remarks: ''
})

const pickerOpen = ref(false)
const pickerYear = ref(new Date().getFullYear())
const pickerMonth = ref(new Date().getMonth())
const pickerDay = ref(new Date().getDate())
const pickerHour = ref(12)
const pickerMinute = ref(0)
const pickerMeridiem = ref('AM')

const weekDays = ['S', 'M', 'T', 'W', 'T', 'F', 'S']
const hourOptions = Array.from({ length: 12 }, (_, i) => i + 1)
const minuteOptions = Array.from({ length: 60 }, (_, i) => i)
const meridiemOptions = ['AM', 'PM']

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

const groupedRows = computed(() => {
  const groups = new Map()

  for (const row of rows.value) {
    const groupKey = [
      row.date_distributed ?? '',
      row.distributed_time ?? '',
      row.recipient_name ?? '',
      row.position ?? '',
      row.affiliation_agency ?? '',
      row.issued_by ?? '',
      row.received_by ?? ''
    ].join('||')

    if (!groups.has(groupKey)) {
      groups.set(groupKey, {
        groupKey,
        date_distributed: row.date_distributed,
        distributed_time: row.distributed_time,
        recipient_name: row.recipient_name,
        position: row.position,
        affiliation_agency: row.affiliation_agency,
        issued_by: row.issued_by,
        received_by: row.received_by,
        itemRows: [],
        primaryRow: row
      })
    }

    groups.get(groupKey).itemRows.push(row)
  }

  return Array.from(groups.values()).map((group) => {
    const sortedItems = [...group.itemRows].sort((a, b) =>
      String(a.volume ?? '').localeCompare(String(b.volume ?? ''), undefined, { numeric: true, sensitivity: 'base' })
    )
    const itemsLabel = sortedItems
      .map((item) => String(item.volume ?? '').trim())
      .filter(Boolean)
      .join(', ')
    const copiesLabel = sortedItems
      .map((item) => String(item.no_of_copies ?? '').trim())
      .filter(Boolean)
      .join(', ')
    const remarksLabel = sortedItems
      .map((item) => String(item.remarks ?? '').trim())
      .filter(Boolean)
      .join(' | ')

    return {
      ...group,
      itemRows: sortedItems,
      itemsLabel,
      copiesLabel,
      remarksLabel
    }
  })
})

const filteredRows = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return groupedRows.value

  return groupedRows.value.filter((row) => {
    const haystack = [
      formatDate(row.date_distributed),
      formatTime(row.distributed_time),
      row.recipient_name,
      row.position,
      row.affiliation_agency,
      row.itemsLabel,
      row.copiesLabel,
      row.remarksLabel,
      row.issued_by,
      row.received_by
    ]
      .map((v) => String(v ?? '').toLowerCase())
      .join(' ')

    return haystack.includes(q)
  })
})

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString()
}

const formatTime = (value) => {
  if (!value) return '-'
  const date = new Date(`1970-01-01T${value}`)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const escapeHtml = (value) =>
  String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')

const printRecord = (row) => {
  const popup = window.open('', '_blank', 'width=900,height=700')
  if (!popup) return
  const itemRows = Array.isArray(row.itemRows) && row.itemRows.length ? row.itemRows : [row]
  const printableItemRows = itemRows.slice(0, 6)
  while (printableItemRows.length < 6) {
    printableItemRows.push({ volume: '', no_of_copies: '', remarks: '' })
  }

  const html = `
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8" />
        <title>MJSIR Acknowledgement</title>
        <style>
          @page { size: A4; margin: 12mm; }
          body { font-family: Arial, sans-serif; margin: 0; color: #111; }
          .sheet { border: 1px solid #777; padding: 14px; min-height: 270mm; box-sizing: border-box; }
          .top { display: grid; grid-template-columns: 1fr 270px; gap: 8px; }
          .brand { border: 1px solid #888; padding: 8px; text-align: center; }
          .brand-title { font-size: 28px; font-weight: 700; letter-spacing: 1px; }
          .brand-sub { font-size: 24px; font-weight: 700; letter-spacing: 1px; margin-top: 2px; }
          .meta table, .main table, .items, .signatures table { width: 100%; border-collapse: collapse; }
          .meta td, .meta th, .main td, .main th, .items td, .items th, .signatures td {
            border: 1px solid #888; padding: 6px 8px; font-size: 13px; vertical-align: top;
          }
          .meta th, .main th, .items th { background: #f8f8f8; text-transform: uppercase; letter-spacing: .6px; font-size: 12px; }
          .main { margin-top: 10px; }
          .items { margin-top: 12px; table-layout: fixed; }
          .items th:nth-child(1), .items td:nth-child(1) { width: 48%; }
          .items th:nth-child(2), .items td:nth-child(2) { width: 20%; text-align: center; }
          .items th:nth-child(3), .items td:nth-child(3) { width: 32%; }
          .blank-row td { height: 26px; }
          .signatures { margin-top: 34px; }
          .line { border-bottom: 1px solid #111; margin-top: 24px; }
          .sig-title { margin-top: 8px; font-size: 12px; font-style: italic; }
          .note { margin-top: 42px; font-size: 13px; text-align: center; line-height: 1.5; }
          .note a { color: #0b62c4; text-decoration: underline; }
          .foot { margin-top: 46px; font-size: 13px; line-height: 1.5; }
        </style>
      </head>
      <body>
        <div class="sheet">
          <div class="top">
            <div class="brand">
              <div class="brand-title">MJSIR</div>
              <div class="brand-sub">ACKNOWLEDGEMENT</div>
            </div>
            <div class="meta">
              <table>
                <tr><th>Code</th><th>Revision Number</th></tr>
                <tr><td>OF-REPO-05</td><td>&nbsp;</td></tr>
                <tr><th>Date of Effectivity</th><th>&nbsp;</th></tr>
                <tr><td>July 17, 2018</td><td>&nbsp;</td></tr>
              </table>
            </div>
          </div>

          <div class="main">
            <table>
              <tr><th style="width: 18%;">Date</th><td style="width: 32%;">${escapeHtml(formatDate(row.date_distributed))}</td><th style="width: 18%;">Time</th><td>${escapeHtml(formatTime(row.distributed_time))}</td></tr>
              <tr><th>Name</th><td colspan="3">${escapeHtml(row.recipient_name || '')}</td></tr>
              <tr><th>Position</th><td colspan="3">${escapeHtml(row.position || '')}</td></tr>
              <tr><th>Affiliation/Agency</th><td colspan="3">${escapeHtml(row.affiliation_agency || '')}</td></tr>
            </table>
          </div>

          <table class="items">
            <tr><th>Volume</th><th>No. of Copies</th><th>Remark</th></tr>
            ${printableItemRows
              .map(
                (item) => `
            <tr${item.volume || item.no_of_copies || item.remarks ? '' : ' class="blank-row"'}>
              <td>${escapeHtml(item.volume || '')}</td>
              <td>${escapeHtml(item.no_of_copies ?? '')}</td>
              <td>${escapeHtml(item.remarks || '')}</td>
            </tr>`
              )
              .join('')}
          </table>

          <div class="signatures">
            <table>
              <tr>
                <td style="width: 50%;">
                  <div><strong>ISSUED BY:</strong> ${escapeHtml(row.issued_by || '')}</div>
                  <div class="line"></div>
                  <div class="sig-title">Signature over printed name</div>
                </td>
                <td style="width: 50%;">
                  <div><strong>RECEIVED BY:</strong> ${escapeHtml(row.received_by || '')}</div>
                  <div class="line"></div>
                  <div class="sig-title">Signature over printed name</div>
                </td>
              </tr>
            </table>
          </div>

          <div class="note">
            Kindly return this acknowledgement receipt after signing. Kindly send back to<br/>
            <a href="mailto:repo@bsu.edu.ph">repo@bsu.edu.ph</a> or (074) 422-1877.<br/>
            Thank you very much!
          </div>

          <div class="foot">
            Please accomplish this form in two copies:<br/>
            1) REPO Copy<br/>
            2) Receiving Copy
          </div>
        </div>
      </body>
    </html>
  `

  popup.document.open()
  popup.document.write(html)
  popup.document.close()
  popup.focus()
  popup.onload = () => popup.print()
}

const deleteRecord = async (row) => {
  const confirmed = window.confirm('Delete this record? This action cannot be undone.')
  if (!confirmed) return

  try {
    const itemRows = Array.isArray(row.itemRows) && row.itemRows.length ? row.itemRows : [row]
    const ids = [...new Set(itemRows.map((item) => item.id).filter(Boolean))]
    await Promise.all(ids.map((id) => axios.delete(`${apiBase}/mjsir-acknowledgements/${id}`)))
    pageMessage.value = 'Record deleted successfully.'
    pageError.value = ''
    await fetchRows()
  } catch (err) {
    pageError.value = err?.response?.data?.message || 'Failed to delete record.'
    pageMessage.value = ''
  }
}

const pad2 = (n) => String(n).padStart(2, '0')

const displayDateTime = computed(() => {
  if (!form.value.date_distributed || !form.value.distributed_time) return 'Click to select date and time'
  return `${formatDate(form.value.date_distributed)} ${formatTime(form.value.distributed_time)}`
})

const monthYearLabel = computed(() =>
  new Date(pickerYear.value, pickerMonth.value, 1).toLocaleDateString([], { month: 'long', year: 'numeric' })
)

const calendarCells = computed(() => {
  const first = new Date(pickerYear.value, pickerMonth.value, 1)
  const start = first.getDay()
  const daysCurrent = new Date(pickerYear.value, pickerMonth.value + 1, 0).getDate()
  const daysPrev = new Date(pickerYear.value, pickerMonth.value, 0).getDate()

  const cells = []
  for (let i = start - 1; i >= 0; i--) {
    cells.push({ day: daysPrev - i, currentMonth: false, monthOffset: -1 })
  }
  for (let d = 1; d <= daysCurrent; d++) {
    cells.push({ day: d, currentMonth: true, monthOffset: 0 })
  }
  while (cells.length < 42) {
    cells.push({ day: cells.length - (start + daysCurrent) + 1, currentMonth: false, monthOffset: 1 })
  }
  return cells
})

const isSelectedDay = (cell) => cell.currentMonth && cell.day === pickerDay.value

const openDateTimePicker = () => {
  if (form.value.date_distributed) {
    const [y, m, d] = form.value.date_distributed.split('-').map(Number)
    pickerYear.value = y
    pickerMonth.value = m - 1
    pickerDay.value = d
  } else {
    const now = new Date()
    pickerYear.value = now.getFullYear()
    pickerMonth.value = now.getMonth()
    pickerDay.value = now.getDate()
  }

  if (form.value.distributed_time) {
    const [h, m] = form.value.distributed_time.split(':').map(Number)
    const isPm = h >= 12
    pickerMeridiem.value = isPm ? 'PM' : 'AM'
    const h12 = h % 12 === 0 ? 12 : h % 12
    pickerHour.value = h12
    pickerMinute.value = m
  }

  pickerOpen.value = true
}

const changeMonth = (delta) => {
  const next = new Date(pickerYear.value, pickerMonth.value + delta, 1)
  pickerYear.value = next.getFullYear()
  pickerMonth.value = next.getMonth()
  const maxDay = new Date(pickerYear.value, pickerMonth.value + 1, 0).getDate()
  if (pickerDay.value > maxDay) pickerDay.value = maxDay
}

const selectDay = (cell) => {
  if (cell.currentMonth) {
    pickerDay.value = cell.day
    return
  }

  changeMonth(cell.monthOffset)
  pickerDay.value = cell.day
}

const clearDateTime = () => {
  form.value.date_distributed = ''
  form.value.distributed_time = ''
  pickerOpen.value = false
}

const setTodayNow = () => {
  const now = new Date()
  pickerYear.value = now.getFullYear()
  pickerMonth.value = now.getMonth()
  pickerDay.value = now.getDate()
  const h = now.getHours()
  pickerMeridiem.value = h >= 12 ? 'PM' : 'AM'
  pickerHour.value = h % 12 === 0 ? 12 : h % 12
  pickerMinute.value = now.getMinutes()
}

const applyDateTime = () => {
  const hour24 = pickerMeridiem.value === 'PM'
    ? (pickerHour.value % 12) + 12
    : (pickerHour.value % 12)

  form.value.date_distributed = `${pickerYear.value}-${pad2(pickerMonth.value + 1)}-${pad2(pickerDay.value)}`
  form.value.distributed_time = `${pad2(hour24)}:${pad2(pickerMinute.value)}`
  pickerOpen.value = false
}

const fetchRows = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get(`${apiBase}/mjsir-acknowledgements`)
    rows.value = response.data?.data || []
  } catch (err) {
    rows.value = []
    error.value = err?.response?.data?.message || 'Failed to load MJSIR acknowledgement records.'
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.value = {
    date_distributed: '',
    distributed_time: '',
    recipient_name: '',
    position: '',
    affiliation_agency: '',
    issued_by: '',
    received_by: '',
    volume: '',
    no_of_copies: 1,
    remarks: ''
  }
  pickerOpen.value = false
}

const createRow = async () => {
  submitting.value = true
  submitError.value = ''
  submitMessage.value = ''
  pageError.value = ''
  pageMessage.value = ''
  try {
    if (!form.value.date_distributed || !form.value.distributed_time) {
      submitError.value = 'Please select both date and time.'
      return
    }

    if (!form.value.volume || !form.value.no_of_copies) {
      submitError.value = 'Volume and No. of Copies are required.'
      return
    }
    if (!form.value.remarks || !form.value.issued_by || !form.value.received_by) {
      submitError.value = 'Remarks, Issued By, and Received By are required.'
      return
    }

    const payload = {
      date_distributed: form.value.date_distributed,
      distributed_time: form.value.distributed_time || null,
      recipient_name: form.value.recipient_name,
      position: form.value.position,
      affiliation_agency: form.value.affiliation_agency,
      issued_by: form.value.issued_by || null,
      received_by: form.value.received_by || null,
      volume: form.value.volume || null,
      no_of_copies: form.value.no_of_copies || 1,
      remarks: form.value.remarks || null
    }

    await axios.post(`${apiBase}/mjsir-acknowledgements`, payload)
    submitMessage.value = 'Record added successfully.'
    resetForm()
    showModal.value = false
    await fetchRows()
  } catch (err) {
    submitError.value = err?.response?.data?.message || 'Failed to add record.'
  } finally {
    submitting.value = false
  }
}

const openAddVolumeModal = (row) => {
  pageError.value = ''
  pageMessage.value = ''
  submitError.value = ''
  submitMessage.value = ''
  const itemRows = Array.isArray(row.itemRows) && row.itemRows.length ? row.itemRows : [row]
  addVolumeSource.value = itemRows[0]
  editSourceItemIds.value = itemRows.map((item) => item.id).filter(Boolean)
  editForm.value = {
    date_distributed: row.date_distributed || '',
    distributed_time: row.distributed_time || '',
    recipient_name: row.recipient_name || '',
    position: row.position || '',
    affiliation_agency: row.affiliation_agency || '',
    issued_by: row.issued_by || '',
    received_by: row.received_by || '',
    items: itemRows.map((item) => ({
      id: item.id || null,
      volume: item.volume || '',
      no_of_copies: item.no_of_copies || 1,
      remarks: item.remarks || ''
    }))
  }
  showAddVolumeModal.value = true
}

const closeAddVolumeModal = () => {
  showAddVolumeModal.value = false
  addVolumeSource.value = null
  editSourceItemIds.value = []
  submitError.value = ''
  submitMessage.value = ''
}

const addEditItem = () => {
  editForm.value.items.push({ id: null, volume: '', no_of_copies: 1, remarks: '' })
}

const removeEditItem = (index) => {
  if (editForm.value.items.length === 1) return
  editForm.value.items.splice(index, 1)
}

const submitEditRecord = async () => {
  submitError.value = ''
  submitMessage.value = ''
  pageError.value = ''
  pageMessage.value = ''

  if (!addVolumeSource.value) {
    submitError.value = 'No source row selected.'
    return
  }

  if (!editForm.value.date_distributed || !editForm.value.distributed_time) {
    submitError.value = 'Date and Time are required.'
    return
  }

  for (let i = 0; i < editForm.value.items.length; i++) {
    const item = editForm.value.items[i]
    if (!item.volume || !item.no_of_copies || !item.remarks) {
      submitError.value = `Item ${i + 1}: Volume, No. of Copies, and Remark are required.`
      return
    }
  }
  if (!editForm.value.issued_by || !editForm.value.received_by) {
    submitError.value = 'Issued By and Received By are required.'
    return
  }

  const base = {
    date_distributed: editForm.value.date_distributed,
    distributed_time: editForm.value.distributed_time || null,
    recipient_name: editForm.value.recipient_name || null,
    position: editForm.value.position || null,
    affiliation_agency: editForm.value.affiliation_agency || null,
    issued_by: editForm.value.issued_by || null,
    received_by: editForm.value.received_by || null
  }

  const itemsWithIds = editForm.value.items.filter((item) => item.id)
  const newItems = editForm.value.items.filter((item) => !item.id)
  const activeIds = new Set(itemsWithIds.map((item) => item.id))
  const removedIds = editSourceItemIds.value.filter((id) => !activeIds.has(id))

  try {
    submitting.value = true
    if (removedIds.length) {
      await Promise.all(removedIds.map((id) => axios.delete(`${apiBase}/mjsir-acknowledgements/${id}`)))
    }

    if (itemsWithIds.length) {
      await Promise.all(
        itemsWithIds.map((item) =>
          axios.put(`${apiBase}/mjsir-acknowledgements/${item.id}`, {
            ...base,
            volume: item.volume,
            no_of_copies: item.no_of_copies,
            remarks: item.remarks || null
          })
        )
      )
    } else if (addVolumeSource.value?.id) {
      await axios.put(`${apiBase}/mjsir-acknowledgements/${addVolumeSource.value.id}`, {
        ...base,
        volume: editForm.value.items[0].volume,
        no_of_copies: editForm.value.items[0].no_of_copies,
        remarks: editForm.value.items[0].remarks || null
      })
    }

    if (newItems.length) {
      await Promise.all(
        newItems.map((item) =>
          axios.post(`${apiBase}/mjsir-acknowledgements`, {
            ...base,
            volume: item.volume,
            no_of_copies: item.no_of_copies,
            remarks: item.remarks || null
          })
        )
      )
    }
    submitMessage.value = 'Record updated successfully.'
    closeAddVolumeModal()
    await fetchRows()
  } catch (err) {
    submitError.value = err?.response?.data?.message || 'Failed to update record.'
  } finally {
    submitting.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  pickerOpen.value = false
  showAddVolumeModal.value = false
  submitError.value = ''
  submitMessage.value = ''
}

const openModal = () => {
  pageError.value = ''
  pageMessage.value = ''
  submitError.value = ''
  submitMessage.value = ''
  showModal.value = true
}

const handleEscKey = (event) => {
  if (event.key !== 'Escape') return
  if (showAddVolumeModal.value) {
    closeAddVolumeModal()
    return
  }
  if (pickerOpen.value) {
    pickerOpen.value = false
    return
  }
  if (showModal.value) closeModal()
}

watch(showModal, async (isOpen) => {
  if (isOpen) {
    previousBodyOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    await nextTick()
    journalTitleInput.value?.focus()
    return
  }
  document.body.style.overflow = previousBodyOverflow || ''
})

onMounted(fetchRows)
onMounted(() => {
  window.addEventListener('keydown', handleEscKey)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscKey)
  document.body.style.overflow = previousBodyOverflow || ''
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>

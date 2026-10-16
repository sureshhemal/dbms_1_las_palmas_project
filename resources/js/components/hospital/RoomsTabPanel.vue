<script lang="ts">
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseConfirmDialog from '@/components/base/BaseConfirmDialog.vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import RoomForm from '@/components/hospital/RoomForm.vue'
import RoomsTable from '@/components/hospital/RoomsTable.vue'
import { Button } from '@/components/ui/button'
import type {
    PaginatedResponse,
    PaginationDetails,
    Room,
    RoomFormData,
    RoomType,
    SelectOption,
} from '@/types'
import { roomTypes } from '@/types'

const freeBedOptions: SelectOption[] = [
    { value: '0', label: 'Full' },
    { value: '1', label: '1 bed free' },
    { value: '2', label: '2 beds free' },
]

export default defineComponent({
    name: 'RoomsTabPanel',

    components: {
        BaseConfirmDialog,
        BaseMultiSelect,
        BasePaginationBar,
        BaseSearchInput,
        BaseSideDrawer,
        Plus,
        RoomForm,
        RoomsTable,
        UiButton: Button,
    },

    data() {
        return {
            rooms: [] as Room[],
            searchTerm: '',
            selectedRoomTypes: [...roomTypes] as RoomType[],
            roomTypes,
            freeBedOptions,
            selectedFreeBeds: freeBedOptions.map((option) => option.value),
            paginationDetails: {
                current_page: 1,
                per_page: 20,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            } as PaginationDetails,
            selectedRoom: null as RoomFormData | null,
            isShowingRoomForm: false,
            isConfirmingDecommission: false,
        }
    },

    computed: {
        decommissionConfirmationDescription(): string {
            return `Room ${this.selectedRoom?.roomID} will no longer take patients. Its record and history are kept.`
        },

        isFilteringByFreeBeds(): boolean {
            return this.selectedFreeBeds.length < freeBedOptions.length
        },

        roomsEmptyMessage(): string {
            return this.searchTerm ||
                this.isFilteringByFreeBeds ||
                this.selectedRoomTypes.length < roomTypes.length
                ? 'No rooms match your search or filter.'
                : 'No rooms yet.'
        },

        roomFormTitle(): string {
            return this.selectedRoom?.id ? 'Edit Room' : 'Add Room'
        },
    },

    watch: {
        searchTerm(): void {
            this.fetchRooms(1)
        },

        selectedRoomTypes(): void {
            this.fetchRooms(1)
        },

        selectedFreeBeds(): void {
            this.fetchRooms(1)
        },
    },

    created() {
        this.fetchRooms()
    },

    methods: {
        async fetchRooms(page?: number, perPage?: number): Promise<void> {
            if (
                this.selectedRoomTypes.length === 0 ||
                this.selectedFreeBeds.length === 0
            ) {
                this.showNoRooms()

                return
            }

            this.$wait.start('fetching-rooms')

            const { data } = await this.$http
                .get<PaginatedResponse<Room>>('rooms', {
                    params: {
                        page: page ?? this.paginationDetails.current_page,
                        per_page: perPage ?? this.paginationDetails.per_page,
                        search: this.searchTerm || undefined,
                        room_types: this.selectedRoomTypes,
                        beds_free: this.isFilteringByFreeBeds
                            ? this.selectedFreeBeds
                            : undefined,
                    },
                })
                .finally(() => this.$wait.end('fetching-rooms'))

            const { data: rooms, ...paginationDetails } = data

            this.rooms = rooms
            this.paginationDetails = paginationDetails
        },

        showNoRooms(): void {
            this.rooms = []
            this.paginationDetails = {
                ...this.paginationDetails,
                current_page: 1,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            }
        },

        toRoomFormData(room: Room): RoomFormData {
            return {
                id: room.id,
                roomID: String(room.roomID),
                roomType: room.roomType,
            }
        },

        addRoom(): void {
            this.selectedRoom = {
                id: null,
                roomID: '',
                roomType: '',
            }

            this.openRoomForm()
        },

        editRoom(room: Room): void {
            this.selectedRoom = this.toRoomFormData(room)

            this.openRoomForm()
        },

        decommissionRoom(room: Room): void {
            this.selectedRoom = this.toRoomFormData(room)

            this.isConfirmingDecommission = true
        },

        openRoomForm(): void {
            this.isShowingRoomForm = true
        },

        closeRoomForm(): void {
            this.isShowingRoomForm = false
        },

        cancelDecommission(): void {
            this.isConfirmingDecommission = false
        },

        async saveRoom(room: RoomFormData): Promise<void> {
            const url = room.id ? `rooms/${room.id}` : 'rooms'
            const method = room.id ? 'put' : 'post'

            this.$wait.start('saving-room')

            await this.$http[method](url, room).finally(() =>
                this.$wait.end('saving-room'),
            )

            this.notifySuccess('Room saved')
            this.closeRoomForm()

            await this.fetchRooms()
        },

        async decommissionSelectedRoom(): Promise<void> {
            if (!this.selectedRoom?.id) {
                return
            }

            this.$wait.start('decommissioning-room')

            await this.$http
                .delete(`rooms/${this.selectedRoom.id}`)
                .finally(() => this.$wait.end('decommissioning-room'))

            this.notifySuccess('Room decommissioned')
            this.isConfirmingDecommission = false

            await this.fetchRooms()
        },
    },
})
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <div class="flex flex-1 items-center gap-2">
                <BaseSearchInput
                    v-model="searchTerm"
                    placeholder="Search rooms by ID"
                />

                <BaseMultiSelect
                    v-model="selectedRoomTypes"
                    :options="roomTypes"
                    placeholder="No room types selected"
                    all-selected-label="All room types"
                />

                <BaseMultiSelect
                    v-model="selectedFreeBeds"
                    :options="freeBedOptions"
                    placeholder="No availability selected"
                    all-selected-label="Any availability"
                />
            </div>

            <UiButton :disabled="$wait.any() > 0" @click="addRoom">
                <Plus />
                Add Room
            </UiButton>
        </div>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchRooms"
            @change-per-page="fetchRooms(1, $event)"
        />

        <RoomsTable
            :rooms="rooms"
            :empty-message="roomsEmptyMessage"
            :is-loading="$wait.is('fetching-rooms')"
            @edit="editRoom"
            @decommission="decommissionRoom"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchRooms"
            @change-per-page="fetchRooms(1, $event)"
        />

        <BaseSideDrawer
            v-if="selectedRoom"
            :is-open="isShowingRoomForm"
            :title="roomFormTitle"
            @close="closeRoomForm"
        >
            <RoomForm :room="selectedRoom" @save="saveRoom" />
        </BaseSideDrawer>

        <BaseConfirmDialog
            :is-open="isConfirmingDecommission"
            title="Decommission room?"
            :description="decommissionConfirmationDescription"
            confirm-label="Decommission"
            @confirm="decommissionSelectedRoom"
            @cancel="cancelDecommission"
        />
    </div>
</template>

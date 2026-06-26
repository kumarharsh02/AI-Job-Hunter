<div id="addJobModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="addJobTitle" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeAddJobModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-slate-900" id="addJobTitle">
                        Add Job Listing
                    </h3>
                    <button type="button" onclick="closeAddJobModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form id="addJobForm" method="POST" action="{{ route('jobs.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <x-input-label for="title" :value="'Job Title *'" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" required autofocus placeholder="Senior Software Engineer" />
                            <x-input-error :messages="$errors->get('title')" class="mt-1" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="company_name" :value="'Company Name *'" />
                            <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" required placeholder="Google, Microsoft, etc." />
                            <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="source_url" :value="'Job URL *'" />
                            <x-text-input id="source_url" class="block mt-1 w-full" type="url" name="source_url" required placeholder="https://linkedin.com/jobs/view/123456" />
                            <x-input-error :messages="$errors->get('source_url')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="location" :value="'Location'" />
                            <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" placeholder="Bangalore, India" />
                            <x-input-error :messages="$errors->get('location')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="work_mode" :value="'Work Mode'" />
                            <select id="work_mode" name="work_mode" class="block mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                <option value="onsite">On-site</option>
                                <option value="remote">Remote</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                            <x-input-error :messages="$errors->get('work_mode')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="employment_type" :value="'Employment Type'" />
                            <select id="employment_type" name="employment_type" class="block mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                <option value="full-time">Full-time</option>
                                <option value="part-time">Part-time</option>
                                <option value="contract">Contract</option>
                                <option value="freelance">Freelance</option>
                                <option value="internship">Internship</option>
                            </select>
                            <x-input-error :messages="$errors->get('employment_type')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="currency" :value="'Currency'" />
                            <select id="currency" name="currency" class="block mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                <option value="INR">INR (₹)</option>
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                            </select>
                            <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="salary_min" :value="'Salary Min (LPA)'" />
                            <x-text-input id="salary_min" class="block mt-1 w-full" type="number" name="salary_min" step="0.01" min="0" placeholder="8.00" />
                            <x-input-error :messages="$errors->get('salary_min')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="salary_max" :value="'Salary Max (LPA)'" />
                            <x-text-input id="salary_max" class="block mt-1 w-full" type="number" name="salary_max" step="0.01" min="0" placeholder="15.00" />
                            <x-input-error :messages="$errors->get('salary_max')" class="mt-1" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="description" :value="'Job Description *'" />
                            <textarea id="description" name="description" required rows="6"
                                      class="block mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white shadow-sm"
                                      placeholder="Paste the full job description here..."></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-1" />
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3 rounded-b-2xl">
                <button type="button" onclick="document.getElementById('addJobForm').submit()"
                        class="w-full inline-flex justify-center items-center gap-2 rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-base font-medium text-white hover:from-indigo-700 hover:to-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Job
                </button>
                <button type="button" onclick="closeAddJobModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function closeAddJobModal() {
        document.getElementById('addJobModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAddJobModal();
    });
</script>
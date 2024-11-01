<div class="bootstrap-srm">
    <div class="srm-settings srm-instructions" id="srm-testimonial-instructions">
        <div class="card w-100 srm-testimonial-instructions-card" style="max-width: 100%; padding:0">
            <h6 class="card-header">Testimonial</h6>
            <div class="card-body">
                This is required and tells Starfish Reviews that the Testimonial feature is being requested.
                <br/><br/>
                <ul class="fa-ul">
                    <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>form = return the Testimonial Form for capturing new testimonials</li>
                    <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>display = return the Testimonials for front-end display</li>
                </ul>
                <div style="border: thin dashed orange; padding: 10px; border-radius: 5px;"><i style="font-size: 20px; color: orange;" class="fa-solid fa-triangle-exclamation"></i> If no value is provided it will default to "form"</div>
            </div>
            <div class="card-footer text-muted"><i style="font-size: 20px; color: darkred;" class="fa-solid fa-bullhorn" title="Example"></i>&nbsp;&nbsp;<code>[starfish testimonial="form"]</code> (default) or <code>[starfish testimonial="display"]</code></div>
        </div>
        <h5>Capturing Testimonials</h5>
        <div class="row">
            <div class="col-sm-4">
                <div class="card srm-testimonial-instructions-card" style="padding:0">
                    <h6 class="card-header">Category</h6>
                    <div class="card-body">
                        Whatever value is placed in this attribute will be used as a Category for all testimonials submitted using this instance of the form.
                        <br/><br/> <div style="border: thin dashed orange; padding: 10px; border-radius: 5px;"><i style="font-size: 20px; color: orange;" class="fa-solid fa-triangle-exclamation"></i> This value (if provided) will also be used as the "Subject" of the Testimonial Form (if that component is used).</div>
                    </div>
                    <div class="card-footer text-muted"><i style="font-size: 20px; color: darkred;" class="fa-solid fa-bullhorn" title="Example"></i>&nbsp;&nbsp;<code>[starfish testimonial="form" category="My Category"]</code></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card srm-testimonial-instructions-card" style="padding:0">
                    <h6 class="card-header">Components</h6>
                    <div class="card-body">
                        Use this attribute to specify the components (form fields) to be displayed in the testimonial form. Provide a comma-seperated list of the following options:
                        <br/><br/>
                        <ul class="fa-ul">
                            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>subject (requires the <code>category</code> attribute)</li>
                            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>name</li>
                            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>email</li>
                            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>phone</li>
                            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>message</li>
                        </ul>
                    </div>
                    <div class="card-footer text-muted"><i style="font-size: 20px; color: darkred;" class="fa-solid fa-bullhorn" title="Example"></i>&nbsp;&nbsp;<code>[starfish testimonial="form" components="name,email,message"]</code></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card srm-testimonial-instructions-card" style="padding:0">
                    <h6 class="card-header">Required</h6>
                    <div class="card-body">
                        Use this attribute to specify which of the components (form fields) are required before the Testimonial can be submitted.  Applies only to the components being used.
                    </div>
                    <div class="card-footer text-muted"><i style="font-size: 20px; color: darkred;" class="fa-solid fa-bullhorn" title="Example"></i>&nbsp;&nbsp;<code>[starfish testimonial="form" category="My Category" required="name,message"]</code></div>
                </div>
            </div>
        </div>
        <h5>Displaying Testimonials</h5>
        <div class="row">
            <div class="col-sm-4">
                <div class="card srm-testimonial-instructions-card" style="padding:0">
                    <h6 class="card-header">Posts</h6>
                    <div class="card-body">
                        Used when displaying testimonials, provide a comma separated list of post ID's, "all" (default).
                    </div>
                    <div class="card-footer text-muted"><i style="font-size: 20px; color: darkred;" class="fa-solid fa-bullhorn" title="Example"></i>&nbsp;&nbsp;<code>[starfish testimonial="display" posts="1,2,3"]</code></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card srm-testimonial-instructions-card" style="padding:0">
                    <h6 class="card-header">Limit</h6>
                    <div class="card-body">
                        Used when displaying testimonials, limit how many posts are returned (only applies when "all" is used within "posts")
                    </div>
                    <div class="card-footer text-muted"><i style="font-size: 20px; color: darkred;" class="fa-solid fa-bullhorn" title="Example"></i>&nbsp;&nbsp;<code>[starfish testimonial="display" limit="25"]</code></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card srm-testimonial-instructions-card" style="padding:0">
                    <h6 class="card-header">Per Page</h6>
                    <div class="card-body">
                        Used when displaying testimonials, the number of posts per page/slide
                        <br/><br/> <div style="border: thin dashed orange; padding: 10px; border-radius: 5px;"><i style="font-size: 20px; color: orange;" class="fa-solid fa-triangle-exclamation"></i> If not value is provided then all testimonials will be returned with no pagination (could result in long page loads depending on the number of testimonials there are).</div>
                    </div>
                    <div class="card-footer text-muted"><i style="font-size: 20px; color: darkred;" class="fa-solid fa-bullhorn" title="Example"></i>&nbsp;&nbsp;<code>[starfish testimonial="display" per_page="4"]</code></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card srm-testimonial-instructions-card" style="padding:0">
                    <h6 class="card-header">Category</h6>
                    <div class="card-body">
                        Filters testimonials according to the provided category ID.
                        <br/><br/> <div style="border: thin dashed orange; padding: 10px; border-radius: 5px;"><i style="font-size: 20px; color: orange;" class="fa-solid fa-triangle-exclamation"></i> This value will override any specific post IDs if those posts do not have the matching category assigned.</div>
                    </div>
                    <div class="card-footer text-muted"><i style="font-size: 20px; color: darkred;" class="fa-solid fa-bullhorn" title="Example"></i>&nbsp;&nbsp;<code>[starfish testimonial="display" category="my_category"]</code></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card srm-testimonial-instructions-card" style="padding:0">
                    <h6 class="card-header">Components</h6>
                    <div class="card-body">
                        Control what components are shown with each testimonial when displayed. If a valid component is provided, then only the components specified will be shown (plus the star rating).
                        <br/><br/>
                        <ul class="fa-ul">
                            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>name</li>
                            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>avatar</li>
                            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>date</li>
                            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>message</li>
                        </ul>
                    </div>
                    <div class="card-footer text-muted"><i style="font-size: 20px; color: darkred;" class="fa-solid fa-bullhorn" title="Example"></i>&nbsp;&nbsp;<code>[starfish testimonial="display" components="name,message"]</code></div>
                </div>
            </div>
        </div>
        <h5>Defaults</h5>
        <ul class="fa-ul">
            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>If the <code>components</code> attribute is not provided or is left empty, all components will be displayed.</li>
            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>No form fields (<code>components</code>) are required by default.</li>
            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>The <code>testimonial</code> attribute defaults to "form".</li>
            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>The <code>posts</code> attribute defaults to "all".</li>
            <li><span class="fa-li"><i class="fa-solid fa-circle"></i></span>The <code>per_page</code> attribute defaults to "0"; resulting in no pagination.</li>
        </ul>
        <div style="border: thin dashed orange; padding: 10px; border-radius: 5px;"><i style="font-size: 20px; color: orange;" class="fa-solid fa-triangle-exclamation"></i> No matter the values provided for the <code>components</code> attribute the Star Rating component will always be present (and required).</div>
    </div>
</div>
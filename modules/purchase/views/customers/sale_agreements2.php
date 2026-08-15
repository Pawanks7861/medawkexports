<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
    :root {
        --fg: #111827;
        --muted: #4b5563;
        --border: #e5e7eb;
    }

    /* body {
        font-family: "Times New Roman", serif;
        color: var(--fg);
        line-height: 1.5;
        margin: 24px;
    } */

    h1,
    h2,
    h3 {
        margin: 0 0 8px;
    }

    h1 {
        text-align: center;
        text-decoration: underline;
        font-size: 22px;
        margin-bottom: 12px;
    }

    .subtitle {
        text-align: center;
        font-style: italic;
        color: var(--muted);
        margin-bottom: 24px;
    }

    p {
        margin: 8px 0;
    }

    .whereas {
        margin: 14px 0;
    }

    .section-title {
        font-weight: bold;
        text-decoration: underline;
        margin: 16px 0 8px;
    }

    .pair {
        display: flex;
        gap: 12px;
    }

    .pair>div {
        flex: 1;
    }

    .hr {
        border-top: 1px solid var(--border);
        margin: 16px 0;
    }

    ol {
        padding-left: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
    }

    th,
    td {
        border: 1px solid var(--border);
        padding: 6px 8px;
        vertical-align: top;
    }

    th {
        text-align: left;
    }

    .sign-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 24px;
    }

    .sign-box {
        min-height: 120px;
        border: 1px dashed var(--border);
        padding: 12px;
    }

    .small {
        font-size: 13px;
        color: var(--muted);
    }

    @media print {
        .no-print {
            display: none !important;
        }

        body {
            margin: 10mm;
        }
    }

    strong {
        font-weight: 700px;
        color: black;
    }
</style>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body doc-wrapper">

                        <?php
                        // Expect $customer (object) and $customer_id to be available from controller
                        // Action URL: adjust as needed
                        echo form_open(admin_url('purchase/customer/' . $customer_id), ['id' => 'banakhat-rera-form']);
                        ?>
                        <input type="hidden" name="customer_id" value="<?php echo html_escape($customer_id); ?>">
                        <input type="hidden" name="sale_agreements" value="1">
                        <!-- Document Header -->
                        <h1>AGREEMENT FOR SALE</h1>
                        <?php $date_value = (isset($documentation) ? $documentation[0]['date'] : '') ?>
                        <?php $month_value = (isset($documentation) ? $documentation[0]['month'] : '') ?>
                        <?php $year_value = (isset($documentation) ? $documentation[0]['year'] : '') ?>
                        <p>This Agreement for Sale without possession (“Agreement”) Executed today on this <input type="text" name="date" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Date" value="<?php echo $date_value; ?>" /> day of <input type="text" name="month" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Month" value="<?php echo $month_value; ?>" />, <input type="text" name="years" class="form-control input-sm" style="display:inline-block; width:auto;" placeholder="Year" value="<?php echo $year_value; ?>" /> By and Between.</p>

                        <p><strong>KAUTILYA PROPERTIES LLP</strong>, (PAN: ABAFK4198C) a Partnership Firm registered under the Limited Liability Partnership Act, 2008, having its registered office at 30, Lad Society, B/h. Aakash Society, Bodakdev, Ahmedabad, represented through its authorized partner <strong>Mr. Kiran Kamdar</strong>, aged about 50 years, Occupation — Business, residing at 30, Lad Society, B/h. Aakash Society, Bodakdev, Ahmedabad. Hereinafter referred to as the “Promoter” / “Developer” / “Vendor” (which expression shall, unless repugnant to the context, include its present and future partners and their respective heirs, successors and assignees) of the <em>One Part</em>.</p>

                        <p>AND</p>

                        <p><strong><?= $customer->company; ?></strong> (Election Card No. <strong><?= $customer->election_card ?></strong>) (PAN: <strong><?= $customer->pan_card ?></strong>) aged about <strong><?= $customer->age; ?> </strong>years, Occupation: <strong><?= $customer->occupation; ?></strong>, residing at <strong><?= $customer->address ?></strong>.
                            <?php
                            if (!empty($customer2)) { ?>
                                <strong><?= $customer2->company2; ?></strong> (Election Card No. <strong><?= $customer2->election_card_2 ?></strong>) (PAN: <strong><?= $customer2->pan_card_2 ?></strong>) residing at <strong><?= $customer2->address_2 ?></strong>
                            <?php }

                            ?>
                            . Hereinafter referred to as the “Allottee” / “Member” / “Purchaser” (which expression shall include, as applicable, heirs, executors, administrators, assigns; in case of HUF, coparceners and members and their heirs, executors, administrators and assigns; in case of proprietary firm, its sole proprietor and successors; in case of partnership, partners for the time being and from time to time and their heirs, executors, administrators and assigns; and in case of company/body corporate, successors and assigns) of the <em>Other Part</em>.
                        </p>

                        <p>The “Promoter” and “Allottee” are together the “Parties” and individually a “Party”.</p>

        

                        <div class="whereas">
                            <p><strong>A)</strong> WHEREAS the Promoter is seized and possessed of or otherwise well and sufficiently entitled to all that piece or parcel of the Non-agricultural land for Multipurpose use bearing Final Plot No.17 admeasuring 5227 sq.mtrs of preliminary Town Planning Scheme No.216 (Shilaj) comprised of Block No.519 admeasuring 7466 sq.mtrs, situate, lying and being at Mouje Shilaj, Taluka Ghatlodia in the Registration District of Ahmedabad and Sub-District of Ahmedabad-9 (Bopal) (hereinafter referred to as “THE SAID LAND”).</p>
                            <p><strong>B)</strong> WHEREAS Kautilya Properties LLP, a partnership firm i.e. the Promoter has purchased the Said Land viz. Freehold Non-agricultural land for Multipurpose use bearing Final Plot No.17 admeasuring 5227 sq.mtrs of preliminary Town Planning Scheme No.216 (Shilaj) comprised of Block No.519 admeasuring 7466 sq.mtrs, from its owners namely (1) Champaben Ganpatbhai Patel, (2) Bhumikaben D/o. Ganpatbhai Patel and W/o. Vijaybhai Patel, (3) Ektaben D/o. Ganpatbhai Patel and W/o. Jaykrushna Brahmbhatt, (4) Siddhiben D/o. Ganpatbhai Patel and W/o. Tejalbhai Patel by Sale Deed registered in the Office of the Sub-Registrar of Assurances at Ahmedabad-9 (Bopal) under Serial No. 16855, dated 09/10/2023 and entry to that effect was mutated in the revenue record vide Mutation Entry No.16613, dated 30/10/2023 which was certified by the competent authority on 13/12/2023.</p>
                            <p><strong>C)</strong> WHEREAS The Promoter is fully competent to enter into this Agreement and all the legal formalities with respect to the right, title and interest of the Promoter regarding the Said Land on which Project is to be constructed /have been completed;</p>
                            <p><strong>D)</strong> WHEREAS the promoter has registered the project under the provisions of The Real Estate (Regulation & Development) Act, 2016 and Gujarat Real Estate (Regulation & Development) (General) Rules, 2017 (hereinafter collectively referred to as “SAID ACT”) with the Real Estate Regulatory Authority At Ahmedabad wide Registration No PR/GJ/AHMEDABAD/AHMEDABADCITY/ AhmedabadMunicipalCorporation/MAA15364/230625/311229 dated 23-06-2025 for the land admeasuring 5227 sq.mtrs., authenticated copy is attached as “<strong>Annexure B</strong>”</p>
                            <p><strong>E)</strong> The Parties herein, relying on the confirmations, representations and assurances of each other to faithfully abide by all the terms, conditions and stipulations contained in this Agreement and all applicable laws, are now willing to enter into this Agreement on the terms and conditions appearing hereinafter;</p>
                            <p><strong>F)</strong> AND WHEREAS The necessary plans for construction of a Residential and Commercial scheme known as “Kautilya Two20”, together with all common amenities and facilities provided therein, hereinafter for the sake of brevity referred to as the “said scheme/project/building” are approved by Assistant Town Development Officer, Ahmedabad Municipal Corporation, Ahmedabad vide its Case No. BHNTI/NWZ/070125/CGDCRV/A8910/R0/M1 and Rajachitthi No. 06686/070125/A8910/R0/M1 for Block No. A+B and vide its Case No. BHNTS/NWZ/070125/CGDCRV/A8911/R0/M1 and Rajachitthi No. 06687/070125/A8911/R0/M1 for Block No. C+D. dated 07/05/2025, which is seen and verified by the Allottee and fully satisfied with the same. The Promoter agrees and undertakes that it shall not make any changes to these approved plans except in strict compliance with section 14 of the said Act and other laws as applicable and the authenticated copies of the plans and specifications has been attached as “Annexure A”.</p>
                            <p><strong>G)</strong> AND WHEREAS the Allottee has/have gone through various documents and papers and Plans thoroughly and have fully understood the contents, terms and conditions and other details of the scheme kept with the Promoter at its office at the above mentioned address. The Allottee has/ have also gone through and has/have made himself/herself/themselves aware of the specifications for construction of the said scheme, more particularly described in the Schedule hereunder written.</p>
                            <p><strong>H)</strong> The Promoter has proposed to develop the said land by launching a scheme of Residential and Commercial Units and for the said purpose the Promoter is doing work of development and construction of Residential Flats and commercial units for the Allottee/s of the scheme known as “Kautilya Two20”. Hereinafter referred to as the “said Scheme”.</p>

                            <p>The said land is earmarked for specific development as mentioned herein and the same shall be used for those purposes only and no other development shall be permitted unless it is a part of the plan approved by the competent authority.</p>

                            <?php
                            $block_name = get_block_name($customer->block_id);
                            $flat_name = get_flat_name($customer->flat_id);
                            $floor_name = get_floor_name($customer->floor_id);
                            $banakhat_details = get_banakhat_details($customer->property_id, $flat_name, $block_name, $floor_name);
                            ?>
                            <p>AND WHEREAS the Allottee being desirous to purchase a residential flats in the said Scheme, approached the Promoter and after verifying all papers, documents, plans, specifications etc. and finding the titles of the Promoter to the said land and construction standing thereon as clear, marketable and free from all encumbrances and beyond reasonable doubts, has decided to purchase the property being <strong>Flat No. <?= $flat_name ?></strong> on the <strong><?= $floor_name ?></strong> of Block “<?= $block_name ?>”, admeasuring <strong><?= $banakhat_details->carpet_area ?> sq.mtrs</strong> carpet area; including balcony <strong><?= $banakhat_details->balcony ?> sq.mtrs</strong> and wash area <strong><?= $banakhat_details->wash_yard ?> sq.mtrs</strong>, together with undivided proportionate land share <strong><?= round($banakhat_details->undivided_land_share, 2) ?> sq.mtrs</strong> in the said land, more particularly described in the Schedule-A. (hereinafter referred to as “<strong>said Shop/Flat</strong>” and/or “<strong>said Premises</strong>” and/or “<strong>said Apartment</strong>”.).</p>

                            <p><strong>I)</strong>AND WHEREAS, Prior to execution, the Allottee paid <strong>Rs. <?= $customer->tokan_amount ?> /-</strong> <strong>(Rupees <?= convertToIndianCurrency($customer->tokan_amount) ?> only)</strong>, being part payment of the sale consideration of the said flat agreed to be sold by the Promoter to the Allottee as advance payment or Application Fee (the payment and receipt whereof the Promoter hereby admits and acknowledges) and the Allottee has agreed to pay to the Promoter the balance of the sale consideration in the manner hereinafter appearing.</p>
                            <p><strong>J)</strong>AND WHEREAS, under section 13 of the said Act, the Promoter is required to execute a written Agreement for sale of the said flat to the Allottee, and to register this Agreement under the Registration Act, 1908.</p>
                        </div>

                        <div class="section-title">Now this Agreement Witnesseth as follows</div>

                        <ol>
                            <li><strong>1.</strong> The scheme will be constructed and completed in accordance with the approved layout plans by the competent authority, which the Allottee has/have seen and approved and the Allottee has also agreed that the Promoter may make such variations and modifications therein as may be required to be done by the Government, Ahmedabad Municipal Corporation and other local authorities and/or which the Promoter/Developer may consider desirable and this shall operate as an irrevocable consent of the Allottee/s for making such variations and modifications.</li>
                            <li><strong>2.</strong> The Allottee has/have satisfied himself/herself/ themselves about the title of the said land/property and the Allottee shall not be entitled to investigate further the titles of the said land/property and no requisition or objection shall be raised in any matter relating thereto.</li>
                            <?php $sum_consideration_amount = (isset($documentation) ? $documentation[0]['sum_consideration_amount'] : '') ?>
                            <li><strong>3.</strong> The Allottee hereby agree/s/agreed to acquire the said/property as per the plans and specifications seen and approved by Allottee and the Developer/ Promoter/Land Owner agrees to allot the said Premises/ flat to the Allottee/s-Purchaser/s at or for the lump sum consideration price of Rs. <input type="number" name="sum_consideration_amount" value="<?= $sum_consideration_amount ?>" /> /-. and the said consideration amount is basic amount i.e. the allottee shall be liable to pay running maintenance, stamp duty, registration fees maintenance deposit, UGVCL, AUDA, legal charges etc. separately..</li>
                            <li>
                                <strong>4. Payment Plan</strong>:
                                <p>The Allottee has paid to Promoter a sum of following amounts of the entire negotiated lump sum consideration towards the Booking Amount / Earnest Money to the Said Developer as per the details mentioned below:</p>
                                <table>
                                    <thead>
                                        <tr>
                                            <th style="width:28%;">Amount (Rs.)</th>
                                            <th>Amount (in words)</th>
                                            <th>Bank Name</th>
                                            <th>Cheque No. / UTR</th>
                                            <th style="width:16%;">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?= $customer->amount ?></td>
                                            <td><?= convertToIndianCurrency($customer->amount) ?></td>
                                            <td><?= $customer->bank_name ?></td>
                                            <td><?= $customer->cheque_no ?></td>
                                            <td>
                                                <?php
                                                if (!empty($customer->payment_date)) {
                                                    echo date('d M, Y', strtotime($customer->payment_date));
                                                } else {
                                                    echo 'N/A'; // or whatever placeholder you prefer
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"><strong>Total:</strong> <?= $customer->amount ?>/- (<?= convertToIndianCurrency($customer->amount) ?> only)</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p>The Said Developer hereby acknowledges the receipt of the same and admits that the said amount shall be adjusted against the total consideration at the time of execution of Sale Deed. </p>
                                <p>The total consideration in respect of the Said Property shall be payable by the Party of the Second Part as per the payment schedule mentioned below :-</p>
                                <ol type="i">
                                    <li>i. 30% of the total consideration to be paid to the Promoter within 7 days from execution of this Agreement.</li>
                                    <li>ii.45% amount of the total consideration to be paid to the Promoter on completion of the Plinth of the building or wing in which the said flat is located.</li>
                                    <li>iii. 70% amount of the total consideration to be paid to the Promoter on completion of the slabs including podiums and stilts of the building or wing in which the said flat is located.</li>
                                    <li>iv. 75% amount of the total consideration to be paid to the Promoter on completion of the walls, internal plaster, floorings doors and windows of the said flat.</li>
                                    <li>v. 80% amount of the total consideration to be paid to the Promoter on completion of the Sanitary fittings, staircases, lift, wells, lobbies upto the floor level of the said flat.</li>
                                    <li>vi. 85% amount of the total consideration to be paid to the Promoter on completion of the external plumbing and external plaster, elevation, terraces with waterproofing, of the building or wing in which the said un flat is located.</li>
                                    <li>vii. 95% amount of the total consideration to be paid to the Promoter on completion of the lifts, water pumps, electrical fittings, electro, mechanical and environment requirements, entrance lobby/s, plinth protection, paving of areas appertain and all other requirements as may be prescribed in the Agreement of sale of the building or wing in which the said flat is located.</li>
                                    <li>viii. Balance Amount against and at the time of handing over of the possession of the said flat to the Allottee on or after receipt of occupancy certificate or completion certificate.</li>
                                </ol>
                            </li>
                            <li>
                                <strong>5. Default in Payment</strong>:
                                <ol type="a">
                                    <li>(a) If the allottee fail to make the payment or execute and register the Agreement for Sale within a period of herein above mentioned then promotor shall forfeit 10 % of the said Purchase Consideration as administrative charges and such event Allotment Letter issued by promotor in favour of allottee shall automatically stand cancelled and promotor shall be entitled to sell or in any other manner transfer the said Apartment to any third party without any claim/objection from allottee.</li>
                                    <li>(b) The Allottee agrees to pay to the Promoter interest at the rate of SBI MCLR+2% per annum, on all delayed payment that becomes due and payable on part of the Allottee to the Promoter.</li>
                                    <li>(c) Without prejudice to the right of promoter to charge interest in terms of the above mentioned clause, on the Allottee committing default in payment on due date of any amount due and payable by the Allottee to the Promoter under this Agreement (including his/her proportionate share of taxes levied by concerned local authority defaults of payment of instalments, the Promoter shall at his own option, may terminate this Agreement.</li>
                                </ol>
                            </li>
                            <li>
                                <strong>6. Time is Essence</strong>:
                                <ul>
                                    <li>• Time is the essence for both the parties i.e. the Promoter as well as the Allottee. The Promoter shall abide by the time schedule for completing the project and handing over the said flat. The Promoter shall give possession of the said flat to the Allottee on or before December 2029</li>
                                    <li>• If the Promoter fails or neglects to give possession of the said flat to the Allottee on account of reasons beyond his control and of his agents by the aforesaid date then the Promoter shall be liable, on demand to refund to the Allottee the amounts already received by him in respect of the said flat along with the interest</li>
                                    <li>• If the Promoter fails to abide by the time schedule for completing the project and handing over the said flat to the Allottee, the Promoter agrees to pay to the Allottee, who does not intend to withdraw from the project, interest at the rate of SBI MCLR+2% per annum, on all the amounts paid by the Allottee, for every month of delay, till the date of written intimation given to the Allottee for the handing over of the possession or up to the date of handing over of the possession, whichever is earlier.</li>
                                    <li>• Provided that the Promoter shall be entitled to reasonable extension of time for giving delivery of said flat on the aforesaid date, if the completion of building in which the said flat is to be situated is delayed on account of -<br>
                                        (a) war, civil commotion or act of God ;<br>
                                        (b) notice, order, rule, notification of the Government and/or other public or competent authority/court.<br>
                                        (c) Pandemic period<br>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <strong>7. Procedure for Taking Possession</strong>:
                                <ol>
                                    <li>(7.1) The Promoter, upon obtaining the occupancy certificate from the competent authority and the payment made by the Allottee as per the agreement shall offer in writing the possession of the [Shop/Flat], to the Allottee in terms of this Agreement to be taken within 3 (three months from the date of issue of such notice and the Promoter shall give possession of the [Shop/Flat] to the Allottee. The Promoter agrees and undertakes to indemnify the Allottee in case of failure of fulfilment of any of the provisions, formalities, documentation on part of the Promoter. The Allottee agree(s) to pay the maintenance charges as determined by the Promoter or association of allottees, as the case may be. The Promoter on its behalf shall offer the possession to the Allottee in writing within 7 days of receiving the occupancy certificate of the Project.</li>
                                    <li>(7.2) The Allottee shall take possession of the Apartment within 15 days of the written notice from the promoter to the Allottee intimating that the said Apartments are ready for use and occupancy:</li>
                                    <li>(7.3) <strong>Failure of Allottee to take Possession of [Shop/Flat]:</strong> Upon receiving a written intimation from the Promoter as per clause 7.2, the Allottee shall take possession of the [Shop/Flat] from the Promoter by executing necessary indemnities, undertakings and such other documentation as prescribed in this Agreement, and the Promoter shall give possession of the [Shop/Flat] to the allottee. In case the Allottee fails to take possession then allottee shall continue to be liable to pay maintenance charges as applicable from the day of receiving occupancy certificate.</li>
                                    <li>(7.4) If within a period of five years from the date of handing over the Apartment to the Allottee, the Allottee brings to the notice of the Promoter any structural defect in the Apartment or the building in which the Apartment are situated or any defects on account of workmanship, quality or provision of service, then, wherever possible such defects shall be rectified by the Promoter at his own cost and in case it is not possible to rectify such defects, then the Allottee shall be entitled to receive appropriate compensation as per actual expenditure incurred to resolve the defect in the manner as provided under the Act. Notwithstanding anything stated in this clause or elsewhere in this Agreement, the Promoter shall not be in any way liable to repair or provide compensation for Structural Defects as set out in this clause where the Allottee has made any structural changes in the Unit or in the materials used thereon..</li>
                                </ol>
                            </li>
                            <li><strong>8.</strong> The Promoter shall in respect of any amount remaining unpaid by the Allottee under the terms and conditions of this Agreement have a first lien and charge on the said, premises to be acquired by the Member/s-Purchaser/s.</li>
                            <li><strong>9.</strong> The Allottee shall have no claim save and except in respect of the said premises agreed to be acquired by him/her i.e. open spaces, general parking, open land, common open places etc. will remain the property of the Promoter subject to the right of the Allottee as hereinafter stated and particularly stated in clause herein below.</li>
                            <li><strong>10.</strong> The Promoter shall confirm the final carpet area that has been allotted to the Allottees after the construction of the building is complete and the occupancy certificate is granted by the competent authority, by furnishing details of the changes, if any, in the carpet area, subject to a variation cap of Three Percent.<br>
                                The total price payable for the carpet area shall be recalculated upon confirmation by the Promoter. if there is any reduction in the carpet area within the defined limit then the Promoter shall refund the excess money paid by the Allottee within forty five days with annual interest at the rate of SBI MCLR+2% from the date when such an excess amount was paid by the Allottees, if there is any increase in the carpet area allotted to the Member/s-Purchaser/s, the Promoter shall demand additional amount from the Allottees as per the next due payment as per the payment schedule. All these monetary adjustment shall be made at the same rate as agreed in clause-3 of this agreement.</li>
                            <li><strong>11.</strong> It is hereby Agreed by the Allottee that the Allottee in whose name the premises shall finally be allotted in pursuance of this Agreement shall deposit with the Promoter such amount as may be fixed ultimately by the Promoter at such time as the Promoter may direct. Said amount shall be utilized by the Promoter towards legal cost, charges and expenses, including professional cost of the attorney–at-law/advocates of the Promoter in connection with formation of the proposed society, or limited company, maintenance agency or apex body or federation and for preparing its rules, regulations and bye-laws and the cost of preparing and engrossing the conveyance or assignment of lease. This deposit amount will be a transferable deposit, in the event of transfer of membership in the said proposed Service Society.</li>
                            <li><strong>12.</strong> After the construction of the said scheme is over and so long as the premises in the said scheme shall not be separately assessed for local taxes and water rates, electric bills etc. Allottees shall pay to the Promoter the sum as may be fixed by the Promoter payable in the manner as may be decided and at the time as may be directed by the Promoter towards the proportionate share of the water tax or other local taxes and outgoing, such as electric bills etc. After the premises in the said scheme are separately assessed.</li>
                            <li><strong>13.</strong> The Promoter hereby agree/s that in the event, if any amount becomes due to the Local Authorities or the State Government or betterment charges or development tax or payment of similar and other nature becoming payable by the Promoter, the same shall be reimbursed by the Promoter in proportion to the area of the said premises agreed to be acquired by the Allottee.</li>
                            <li><strong>14.</strong> The allottee agree/s and undertakes to be a member of the proposed Service Society and also from time to time to see and execute the application and other papers and documents necessary and to fill in, sign and return along with usual amount payable like membership fees, advance maintenance, share money, subscription etc. to the proposed Service Society. The Allottee shall be bound from time to time to see all papers and documents and do all other things as the Promoter may require him/her/them to do from time to time for safeguarding the interest of the Promoter and other Allottees of the premises in the said scheme. Failure to comply with provisions of this clause will render this Agreement, ipso facto to come to an end.</li>
                            <li><strong>15.</strong> The Promoter shall not mortgage or create a charge on the said flat after the execution of the agreement and if any such mortgage or charge is made or created then notwithstanding anything contained in any other law for time being in force, such mortgage or charge shall not affect the right and interest of the Allottees, who has taken or agreed to take said flat.</li>
                            <li><strong>16.</strong> After the possession of the said premises is handed over to the Allottees, if any additions or alteration in or about or relating to the said premises is thereafter required to be carried out by the Government, Ahmedabad Municipal Corporation, Local Authorities or any Statutory Authorities, the same shall be carried out by the Promoter of the premises at his/her/their own costs and the Allottees shall not be in any manner liable or responsible for the same if any addition or alteration is to be made in span of five years.</li>
                            <li><strong>17.</strong> If the Allottee neglects, omits or fails for any reason whatsoever to pay to the Promoter any part of the amounts due and payable by the Allottees under the terms and conditions of Agreement (whether before or after acquiring the possession) within the time hereinbefore specified or if the Allottees shall in any other way fail to perform or observe any of the covenants and stipulations herein contained, the Promoter shall give notice of fifteen days in writing to the Allottees, by registered post AD at the address provided by the Allottees and mail at the E-mail address provided by the Allottees, of his intention to terminate this agreement and of the specific breach or breaches of terms and conditions in respect of which it is intended to terminate the agreement. If the Allottees fail to rectify the breach or breaches mentioned by the Promoter within the period of notice then at the end of such notice period, Promoter shall be entitled to terminate this agreement ex-parte and the Allottee shall cease all its rights over the said flat by virtue of these presents and same shall be unconditionally binding and acceptable to the allottee.</li>
                            <li><strong>18.</strong> It is hereby agreed between the Promoter and the Allottee that no sooner the entire payments of all other dues payable by the Allottee to the Promoter under this Agreement have been duly, properly, completely and finally paid and the Allottee having been enrolled as member of the proposed service Society as provided in this agreement, the member shall be bound to occupy the premises as permitted by the rules, by-laws and/or resolutions of the proposed Service Society. It is agreed by the Allottee that the Allottee will have to compulsorily become the Member-Shareholder of the proposed Service Society.</li>
                            <li><strong>19.</strong> All costs, charges and expenses of the formation of the proposed Service Society as well the costs of preparing engrossing, stamping and registering all the agreements, conveyance or any other documents required to be executed by the Promoter or by the Allottee as well as entire professional costs of the Legal Advisor Attorneys of the Promoter in preparing and approving all such documents shall be borne and paid by the Allottees proportionately upon exclusively. The Promoter shall not contribute anything towards such expenses.</li>
                            <li>
                                <strong>20.</strong> Before and on possession of the said premises being taken over by the Allottees, the Allottees can make or be entitled to make any claim, objection or dispute regarding the quality of construction, materials used therein, any additions or alteration made, plans specifications and designs of the construction within the completion of the construction of five years.
                            </li>
                            <li><strong>21.</strong> “The promoter hereby declares that the floor space index available as on date in respect of the project land is 21197.90 square meters only and promoters has planned to utilize floor space index of 21197.90 square meter by availing of TDR or FSI available on payment of premium or FSI available as incentive FSI by implementing various schemes as mentioned in the development control or based on expectation of increased FSI which may be available in future on modification to development control and regulations, which are applicable to the said project. The promoter has disclosed the floor space Index of 21197.90 square meter as proposed to be utilized by him on the project land in the said project and allottee has agreed to purchase the said apartment based on the proposed construction and sale of apartments to be carried out by the promoter by utilizing the proposed FSI and on the understanding that the “declared” proposed FSI shall belong to promoter only.”.</li>
                            <li><strong>22.</strong> The transaction covered by this agreement at present is not understood to be eligible to tax under any direct or indirect tax laws or similar other laws. If however, by reason of any amendment to the constitution or enactment or amendment of any other law, Central of State, this transaction is held to be eligible to tax, as a sale or otherwise either as a whole or in part or any inputs of materials or equipments used or supplied in execution of or in connection with this transaction are eligible to tax, the same shall be payable by the Allottees on demand at any time.</li>
                            <li>
                                <strong>23. The Allottees has/have specifically agreed, undertaken, accepted and confirmed as follows:-</strong>:
                                <ol type="a">
                                    <li>(a) The over-all control and management of the scheme-project and all and every matters relating thereto shall be that of the Promoter and its decision in all and every matters concerning touching to, with respect to, or in relation there to shall be final and binding upon the Allottees.</li>
                                    <li>(b) The title of the Allottees shall, finally, be as Allottees of the scheme of Promoter and proposed service Society.</li>
                                    <li>(c) The Promoter shall be entitled to allot, deal with and/or dispose of the remaining “PREMISES” in the scheme to such person/s on payment of such amount for such use and purposes, including other than for which it may have been meant, is such manner and on such other terms and conditions, same, similar or other then herein, as per Developer in its sole discretion may deem, fit and proper. The Allottees shall not have any right to dispute, oppose or challenge the same. The expression “PREMISES” shall mean and include constructed or unconstructed, covered or un-covered, open or closed, open margin lands, parking areas-space, other open areas and space, terraces with or without any right to put up further of additional construction amenities, facilities and services forming part of the Scheme – project or otherwise any part or portion of the Scheme – project, any right, title or interest therein.</li>
                                    <li>(d) The purchaser has clearly understood and agreed that the unit-holder of Unit No.A-101, A-104, B-101, B-104, C-101, C-102, C-103, C-104, D-101, D-102, D-103, D-104, A-202, A-203, B-202 and B-203 have got ingress and egress to the terrace. None of the other Unit Holders of the said scheme have any right on this terrace. Another extra terrace will be common for all Unit Holders. Unit Holders of above mentioned unit nos. are not entitled to make any kind of temporary or permanent shade, structure or construction on said terrace.</li>
                                </ol>
                            </li>
                            <li><strong>24. </strong>The Allottee hereby agrees to execute such other papers and documents and also pay necessary stamp duty registration fees and other out of pocket expenses etc., as may be necessary for the purpose of giving effect to these presents.</li>
                            <li><strong>25. </strong>If any provision of this agreement shall be determined to be void or unenforceable under the act or the rules and regulations made thereunder or under other applicable laws, such provisions of the agreement shall be deemed amended or deleted in so far as reasonably inconsistent with the purpose of this agreement and to the extent necessary to conform to act or the rules or regulation made thereunder or the applicable law, as the case may be, and the remaining provisions of this agreement shall remain valid and enforceable as applicable at the time of execution of this agreement. The Allottee and Promoter have to follow the provisions of the RERA Act & Rules.</li>
                            <li><strong>26. </strong>
                                That all notices to be served on the Allottee and the Promoter as contemplated by this Agreement shall be deemed to have been duly served if sent to the Allottee or the Promoter by Registered Post A.D and notified Email ID/Under Certificate of Posting at their respective addresses specified below:
                                <table class="info-table">
                                    <tr>
                                        <th>Name of Allottee:</th>
                                        <td>As above</td>
                                    </tr>
                                    <tr>
                                        <th>Address of Allottee:</th>
                                        <td><span class="as-above">As above</span></td>
                                    </tr>
                                    <tr>
                                        <th>Name of Promoter:</th>
                                        <td>Kautilya Properties LLP</td>
                                    </tr>
                                    <tr>
                                        <th>Address of Promoter:</th>
                                        <td><span class="as-above">As above</span></td>
                                    </tr>
                                </table>
                                <p>It shall be the duty of the Allottee and the promoter to inform each other of any change in address subsequent to the execution of this Agreement in the above address by Registered Post failing which all communications and letters posted at the above address shall be deemed to have been received by the promoter or the Allottee, as the case may be.</p>

                            </li>
                            <li>
                                <strong>27. REPRESENTATIONS AND WARRANTIES OF THE PROMOTER</strong>
                                <p>The Promoter hereby represents and warrants to the Allottee as follows:</p>
                                <ul>
                                    <li>i. The Promoter has clear and marketable title with respect to the project land; and has the right to carry out the developmental rights.</li>
                                    <li>ii. The Promoter has lawful rights and requisite approvals from the competent Authorities to carry out development of the Project and other requisite approvals shall be obtained with time;</li>
                                    <li>iii. There are no encumbrances upon the project land or the Project except those disclosed in the title report:</li>
                                    <li>iv. There are no litigations pending before any Court of law with respect to the project land or Project except those disclosed in the title report;</li>
                                    <li>v. The approvals and licenses issued by the competent authorities with respect to the Project, project land and said building are valid and subsisting.</li>
                                    <li>vi. The Promoter has the right to enter into this Agreement and has not committed or omitted to perform any act or thing, whereby the right, title and interest of the Allottee created herein, may be affected;</li>
                                    <li>vii. The Promoter confirms that it has not entered into any agreement for sale and/or development agreement or any other agreement / arrangement with any person or party with respect to the project land, including the Project and the said flat which will, in any manner, affect the rights of Allottee under this Agreement ;</li>
                                    <li>viii. The Promoter confirms that the Promoter is not restricted in any manner whatsoever from selling the said flat to the Allottee in the manner contemplated in this Agreement;</li>
                                    <li>ix. At the time of execution of the conveyance deed of the structure to the association of allottee, the Promoter shall handover lawful, vacant, peaceful, physical possession of the common structure to the Association of the Allottees;</li>
                                    <li>x. The Promoter has duly paid and shall continue to pay and discharge undisputed governmental dues, rates, charges and taxes and other monies, levies, impositions, premiums, damages and/or penalties and other outgoings, whatsoever, payable with respect to the said project to the competent Authorities up to the date of completion certificate.</li>
                                    <li>xi. The Promoter has not been served upon any notice from the Government or any other local body or authority or any legislative enactment, government ordinance, order, notification regarding the said flat/Scheme.</li>
                                </ul>
                            </li>
                            <li><strong>28. </strong>The Allottee/s or himself/themselves with intention to bring all persons into whosoever hands the said flat may come, hereby covenants with the Promoter as follows :-
                                <ul>
                                    <li>i. The Allottee shall maintain the said flat at his own cost and shall maintain it in good condition.</li>
                                    <li>ii. The allottee undertakes not to keep in the said flat any goods which are of hazardous, combustible or dangerous nature or are so heavy as to damage the construction or structure of the building in which the said flat is situated or storing of which goods is objected to by the concerned local or other authority and shall take care while carrying heavy packages which may damage or likely to damage the staircases, common passages or any other structure of the building in which the said flat is situated.</li>
                                    <li>iii. The allottee undertakes to carry out at his own cost all internal repairs to the said flat and maintain the said flat in the same condition, state and order in which it was delivered by the Promoter to the Allottee.</li>
                                    <li>iv. The allottee undertakes not to demolish or cause to be demolished the said flat or any part thereof, nor at any time make or cause to be made any addition or alteration of whatever nature in or to the said flat or any part thereof, nor any alteration in the elevation and outside colour scheme of the building in which the said flat is situated and shall keep the portion, sewers, drains and pipes in the said flat and the appurtenances thereto in good tenantable repair and condition.</li>
                                    <li>v. The allottee undertakes not to do or permit to be done any act or thing which may render void or voidable any insurance of the project land and the building in which the said flat is situated or any part whereby any increased premium shall become payable in respect of the insurance.</li>
                                    <li>vi. The allottee undertakes not to throw dirt, rubbish, rags, garbage or other refuse or permit the same to be thrown from the said flat in the compound or any portion of the project land and the building in which the said flat is situated.</li>
                                    <li>vii. The allottee undertakes to pay the charges towards stamp duty and registration.</li>
                                </ul>
                            </li>
                            <li><strong>29. BINDING EFFECT :<br></strong>The Agreement shall bind once it is signed by both the parties.</li>
                            <li><strong>30. Dispute Resolution :-<br></strong>Any dispute between parties shall be settled amicably. In case of failure to settled the dispute amicably, which shall be referred to the Competent Authority as per the provisions of the Real Estate (Regulation and Development) Act, 2016 and Rules and Regulations framed there under.</li>
                            <li><strong>31. ENTIRE AGREEMENT :<br></strong>This Agreement, along with its schedules and annexures, constitutes the entire Agreement with respect to the subject matter and supersedes any and all understandings, any other agreements, allotment letter, correspondences, arrangements whether written or oral, if any, between the Parties in regard to the said flat /Land/Building/Scheme, as the case may be.</li>
                            <li><strong>32. RIGHT TO AMEND :<br></strong>This Agreement may only be amended through written consent of the Parties.</li>
                            <li><strong>33. PROVISIONS OF THIS AGREEMENT APPLICABLE TO ALLOTTEE/ SUBSEQUENT ALLOTTEES :<br></strong>It is clearly understood and so agreed by and between the Parties hereto that all the provisions contained herein and the obligations arising hereunder in respect of the Project shall equally be applicable to and enforceable against any subsequent Allottee of the said flat, in case of a transfer, as the said obligations go along with the said flat for all intents and purposes.</li>
                            <li><strong>34. SEVERABILITY :<br></strong>If any provision of this Agreement shall be determined to be void or unenforceable under the Act or the Rules and Regulations made thereunder or under other applicable laws, such provisions of the Agreement shall be deemed amended or deleted in so far as reasonably inconsistent with the purpose of this Agreement and to the extent necessary to conform to Act or the Rules and Regulations made thereunder or the applicable law, as the case may be, and the remaining provisions of this Agreement shall remain valid and enforceable as applicable at the time of execution of this Agreement.</li>
                            <li><strong>35. METHOD OF CALCULATION OF PROPORTIONATE SHARE WHEREVER REFERRED TO IN THE AGREEMENT :<br></strong>Wherever in this Agreement it is stipulated that the Allottee has to make any payment in common with other Allottee(s) in Project, the same shall be in proportion to the carpet area of the said flat/shop to the total carpet area of all the flats/shop in the Project.</li>
                            <li><strong>36. FURTHER ASSURANCES :<br></strong>Both Parties agree that they shall execute, acknowledge and deliver to the other such instruments and take such other actions, in additions to the instruments and actions specifically provided for herein, as may be reasonably required in order to effectuate the provisions of this Agreement or of any transaction contemplated herein or to confirm or perfect any right to be created or transferred hereunder or pursuant to any such transaction.</li>
                            <li><strong>37. PLACE OF EXECUTION :<br></strong>The execution of this Agreement shall be complete only upon its execution by the Promoter through its authorized signatory at the Promoter’s Office, or at some other place, which may be mutually agreed between the Promoter and the Allottee, in after the Agreement is duly executed by the Allottee and the Promoter or simultaneously with the execution the said Agreement shall be registered at the office of the Sub-Registrar. Hence this Agreement shall be deemed to have been executed at Ahmedabad.</li>
                            <li><strong>38. GOVERNING LAW :<br></strong>The rights and obligations of the parties under or arising out of this Agreement shall be construed and enforced in accordance with the laws of India for the time being in force and the courts will have the jurisdiction for this Agreement.</li>
                        </ol>

                        <h1 class="section-title">Schedule “A”</h1>
                        <p>All that piece or parcel of immovable property being Flat No. <strong><?= $flat_name ?></strong>, on the <strong><?= $floor_name ?></strong> Floor of Block <strong>“<?= $block_name ?>”</strong>, admeasuring <strong><?= $banakhat_details->carpet_area ?>sq.mtrs.</strong>  carpet area; including net carpet area of balcony admeasuring <strong><?= $banakhat_details->balcony ?> sq.mtrs.</strong>; wash area admeasuring <strong><?= $banakhat_details->wash_yard ?> sq.mtrs.</strong>; in the scheme known as <strong>“Kautilya Two20”</strong>; together with undivided proportionate share admeasuring <strong><?= round($banakhat_details->undivided_land_share, 2) ?> sq.mtrs.</strong> in the Freehold Non-agricultural land for Multipurpose use bearing Final Plot No.17 admeasuring 5227 sq.mtrs of preliminary Town Planning Scheme No.216 (Shilaj) comprised of Block No.519 admeasuring 7466 sq.mtrs, situate, lying and being at Mouje SHILAJ, Taluka Ghatlodia in the Registration District of Ahmedabad and Sub-District of Ahmedabad-9 (Bopal) together with a right to use common facilities and amenities of the scheme and Said flat is bounded as follows : - </p>
                        <div class="pair small">
                            <div><strong>On or towards the East :</strong> <?= $banakhat_details->east ?><br><strong>On or towards the West :</strong> <?= $banakhat_details->west ?><br><strong>On or towards the North :</strong> <?= $banakhat_details->north ?><br><strong>On or towards the South :</strong> <?= $banakhat_details->south ?></div>
                        </div>
                        <h1 class="section-title">Schedule “B”</h1>
                        <p style="text-align: center;">Floor plan</p>

                        <h1 class="section-title">Annexure “A”</h1>
                        <p style="text-align: center;">Copy of approved lay-out plan</p>

                        <h1 class="section-title">Annexure “B”</h1>
                        <p style="text-align: center;">Registration Certificate of RERA</p>

                        <h1 class="section-title">Annexure “C”</h1>
                        <p>Description of Common Assets in proportion with right to use common amenities and facilities provided for the flat/apartment of the said building and to be used in common with other Allottee of flats and which shall be limited to :-</p>
                        <ul>
                            <li>i. Lifts</li>
                            <li>ii. Garden/Lawn</li>
                            <li>iii. Overhead Water Tank</li>
                            <li>iv. Underground Water Tank</li>
                            <li>v. Pump with Motor</li>
                            <li>vi. Staircase</li>
                            <li>vii. Passage with lights leading to all Floors and Cellar</li>
                            <li>viii.Electric Meter room</li>
                        </ul>


                        <p>Received of and from the Allottee above named the sum of Rupees <strong><?= convertToIndianCurrency($customer->final_amount) ?> only</strong> on execution of this agreement towards Earnest Money Deposit or application fee.</p>

                        <div class="sign-grid">
                            <div class="sign-box">
                                <p><strong>Signed, Sealed and Delivered by the “Promoter”</strong></p>
                                <p><strong>Kautilya Properties LLP</strong><br />through its authorized partner<br />Mr. Kiran Kamdar</p>
                                <p>______________________</p>
                                <p class="small">In the presence of witnesses:<br />1. ____________________<br />2. ____________________</p>
                            </div>
                        </div>


                        <h1 class="section-title">Schedule — As per Section 32(A) of the Registration Act</h1>
                        <div class="pair">
                            <div>
                                <p><strong>Signature, Photograph and Thumb Impression of First Part:</strong></p>
                                <p>__________________</p>
                                <p>__________________</p>
                                <p><strong>Kiranbhai R. Kamdar</strong></p>
                                <p class="small">Kautilya Properties LLP — through its authorized signatory, Kiran Rasiklal Kamdar</p>
                            </div>
                            <div>
                                <p><strong>Signature, Photograph and Thumb Impression of Second Part:</strong></p>
                                <p>__________________</p>
                                <p>__________________</p>
                                <p><strong>________________</strong></p>
                            </div>
                        </div>


                        <div class="btn-bottom-toolbar btn-toolbar-container-out text-right no-print" style="margin-top:16px;">
                            <button class="btn btn-info only-save">
                                <?php echo _l('submit'); ?>
                            </button>
                        </div>

                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
</body>

</html>